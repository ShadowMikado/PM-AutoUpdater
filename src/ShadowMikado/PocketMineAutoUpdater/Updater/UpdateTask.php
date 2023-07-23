<?php

namespace ShadowMikado\PocketMineAutoUpdater\Updater;

use LogLevel;
use pocketmine\scheduler\AsyncTask;

class UpdateTask extends AsyncTask
{

    private const TLS_KEY_AUTOUPDATER = "autoupdater";

    public function __construct(UpdateCheckerMixin $updater)
    {
        $this->storeLocal(self::TLS_KEY_AUTOUPDATER, $updater);
    }

    public function onRun(): void
    {
    }

    public function onCompletion(): void
    {
        /** @var UpdateCheckerMixin2 $updater */
        $updater = $this->fetchLocal(self::TLS_KEY_AUTOUPDATER);
        if ($updater->isConnected() == true) {
            $updater->checkUpdate();
            $updater->downloadUpdate();
            $this->cancelRun();
        } else {
            $updater->printConsoleMessage("The server isn't connected to the internet !", LogLevel::ALERT);
            $this->cancelRun();
        }
    }
}
