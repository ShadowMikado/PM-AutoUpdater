<?php

namespace ShadowMikado\PocketMineAutoUpdater\Updater;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;

class UpdateTask extends AsyncTask
{
    private const TLS_KEY_AUTO_UPDATER = "autoupdater";
    private string $error = "Unknown error";

    public function __construct(UpdateCheckerMixin $updater)
    {
        $this->storeLocal(self::TLS_KEY_AUTO_UPDATER, $updater);
    }

    public function onRun(): void
    {
        if ($this->isConnected()) {
            $error = "";
            $response = Internet::getURL("https://update.pmmp.io/api", 4, [], $error);
            $this->error = $error;

            if ($response !== null) {
                $response = json_decode($response->getBody(), true);
                if (is_array($response)) {
                    if (isset($response["error"]) && is_string($response["error"])) {
                        $this->error = $response["error"];
                    } else {
                        $mapper = new \JsonMapper();
                        $mapper->bExceptionOnMissingData = true;
                        $mapper->bEnforceMapType = false;
                        try {
                            /** @var UpdateInfoMixin $responseObj */
                            $responseObj = $mapper->map($response, new UpdateInfoMixin());
                            $this->setResult($responseObj);
                        } catch (\JsonMapper_Exception $e) {
                            $this->error = "Invalid JSON response data: " . $e->getMessage();
                        }
                    }
                } else {
                    $this->error = "Invalid response data";
                }
            }
        } else {
            $this->error = "No internet connection";
        }
    }

    public function onCompletion(): void
    {
        /** @var UpdateCheckerMixin $updater */
        $updater = $this->fetchLocal(self::TLS_KEY_AUTO_UPDATER);
        if ($this->hasResult()) {
            /** @var UpdateInfoMixin $response */
            $response = $this->getResult();
            $updater->checkUpdateCallback($response);
        } else {
            $updater->checkUpdateError($this->error);
        }
    }

    public function isConnected(): bool
    {
        $connected = @fsockopen("google.com", 443);
        if ($connected) {
            $is_conn = true;
            fclose($connected);
        } else {
            $is_conn = false;
        }
        return $is_conn;
    }
}
