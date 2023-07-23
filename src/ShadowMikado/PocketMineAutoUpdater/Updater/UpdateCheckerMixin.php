<?php

namespace ShadowMikado\PocketMineAutoUpdater\Updater;

use Logger;
use LogLevel;
use pocketmine\Server;
use pocketmine\utils\Internet;
use PrefixedLogger;

class UpdateCheckerMixin
{
	protected Server $server;
	protected string $updateUrl = "";
	private Logger $logger;
	public string $error = "Unknown error";

	public function __construct(Server $server)
	{
		$this->server = $server;
		$this->logger = new PrefixedLogger($server->getLogger(), "Auto Updater");
	}

	public function checkUpdateError(string $error): void
	{
		$this->logger->debug("Async update check failed due to \"$error\"");
	}

	public function printConsoleMessage(string $lines, string $logLevel = LogLevel::INFO): void
	{
		$this->logger->log($logLevel, $lines);
	}

	public function checkUpdate(): void
	{
		$response = $this->getResponse();
		$this->updateUrl = $response["download_url"];
	}


	public function downloadUpdate(): void
	{
		if ($this->isUpdatable()) {
			$pluginData = $this->server->getPluginManager()->getPlugin("PM-AutoUpdater")->getDataFolder();
			$this->logger->critical("Downloading PocketMine update, don't stop the server !");
			file_put_contents($pluginData . "update/PocketMine-MP.phar", fopen($this->updateUrl, "r"));
			if (file_exists("PocketMine-MP.phar")) {
				copy("PocketMine-MP.phar", $pluginData . "old/PocketMine-MP.phar");
				$this->logger->warning("Old PocketMine-MP.phar copied in " . $pluginData . "old");
			} else {
				$this->logger->notice("You've probably deleted old PocketMine-MP.phar, can't make a backup!");
			}
			$this->logger->warning("Successfully downloaded update in " . $pluginData . "update");
		}
	}

	public function getResponse()
	{
		$error = "";
		$response = Internet::getURL("https://update.pmmp.io/api", 4, [], $error);
		$this->error = $error;

		if ($response != null) {
			$response = json_decode($response->getBody(), true);
			if (is_array($response)) {
				if (isset($response["error"]) && is_string($response["error"])) {
					$this->error = $response["error"];
				} else {
					return $response;
				}
			} else {
				$this->error = "Invalid response data (format)";
			}
		} else {
			$this->error = "Invalid response data (null)";
		}
	}

	public function isUpdatable(): bool
	{
		return $this->server->getPocketMineVersion() !== $this->getResponse()["base_version"];
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

	public function doCheck(): void
	{
		$this->server->getAsyncPool()->submitTask(new UpdateTask($this));
	}
}
