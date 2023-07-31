<?php

namespace ShadowMikado\PocketMineAutoUpdater\Updater;

use Logger;
use LogLevel;
use pocketmine\Server;
use PrefixedLogger;

class UpdateCheckerMixin
{
	protected Server $server;
	private Logger $logger;
	protected ?UpdateInfoMixin $updateInfo = null;

	public function __construct(Server $server)
	{
		$this->server = $server;
		$this->logger = new PrefixedLogger($server->getLogger(), "Auto Updater");
	}

	public function checkUpdateError(string $error): void
	{
		$this->logger->critical("Async update check failed due to \"$error\"");
	}

	public function printConsoleMessage(string $lines, string $logLevel = LogLevel::INFO): void
	{
		$this->logger->log($logLevel, $lines);
	}

	public function hasUpdate(): bool
	{
		if ($this->updateInfo !== null) {
			return $this->server->getPocketMineVersion() !== $this->updateInfo->base_version;
		} else {
			return false;
		}
	}

	public function downloadUpdate(): void
	{
		$pluginData = $this->server->getPluginManager()->getPlugin("PM-AutoUpdater")->getDataFolder();
		$this->logger->critical("Downloading PocketMine update, don't stop the server !");
		file_put_contents($pluginData . "update/PocketMine-MP.phar", fopen($this->updateInfo->download_url, "r"));
		if (file_exists("PocketMine-MP.phar")) {
			copy("PocketMine-MP.phar", $pluginData . "old/PocketMine-MP.phar");
			$this->logger->warning("Old PocketMine-MP.phar copied in " . $pluginData . "old");
		} else {
			$this->logger->notice("You've probably deleted old PocketMine-MP.phar, can't make a backup!");
		}
		$this->logger->warning("Successfully downloaded update in " . $pluginData . "update");
	}

	public function getUpdateInfo(): ?UpdateInfoMixin
	{
		return $this->updateInfo;
	}

	public function doCheck(): void
	{
		$this->server->getAsyncPool()->submitTask(new UpdateTask($this));
	}

	protected function checkUpdate(UpdateInfoMixin $updateInfo): void
	{

		if ($this->server->getPocketMineVersion() !== $updateInfo->base_version) {
			$this->updateInfo = $updateInfo;
		} else {
			$this->updateInfo = null;
		}
	}

	public function checkUpdateCallback(UpdateInfoMixin $updateInfo): void
	{
		$this->checkUpdate($updateInfo);
		if ($this->hasUpdate()) {
			$this->downloadUpdate();
		} else {
			$this->logger->debug("No update !");
		}
	}
}
