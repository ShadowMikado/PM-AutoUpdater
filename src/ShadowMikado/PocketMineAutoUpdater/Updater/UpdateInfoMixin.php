<?php

namespace ShadowMikado\PocketMineAutoUpdater\Updater;

final class UpdateInfoMixin
{
	/** @required */
	public string $php_version;
	/** @required */
	public string $base_version;
	/** @required */
	public bool $is_dev;
	/** @required */
	public string $channel;
	/** @required */
	public string $git_commit;
	/** @required */
	public string $mcpe_version;
	/** @required */
	public int $build;
	/** @required */
	public int $date;
	/** @required */
	public string $details_url;
	/** @required */
	public string $download_url;
	/** @required */
	public string $source_url;
}
