<?php

namespace Ceneo\Application\Helper;

class NoticeHelper {
	public function addNotice($type, $message): void {
		$notices = get_transient("ceneo_notices");
		if($notices === false) $notices = [];
		$notices[] = ["type" => $type, "message" => $message];
		set_transient("ceneo_notices", $notices, 10);
	}

	public function getNotices(): array {
		$notices = get_transient("ceneo_notices");
		if($notices === false) return [];
		delete_transient("ceneo_notices");
		return $notices;
	}
}
