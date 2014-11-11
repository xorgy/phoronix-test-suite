<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2014, Phoronix Media
	Copyright (C) 2014, Michael Larabel

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program. If not, see <http://www.gnu.org/licenses/>.
*/


class phoromatic_admin implements pts_webui_interface
{
	public static function page_title()
	{
		return 'Phoromatic Root Administrator';
	}
	public static function page_header()
	{
		return null;
	}
	public static function preload($PAGE)
	{
		return true;
	}
	public static function render_page_process($PATH)
	{
		if($_SESSION['AdminLevel'] != -40)
		{
			header('Location: /?main');
		}

		$main = '<h1>Phoromatic Server Administration</h1>';

		$main .= '<hr /><h2>Server Information</h2>';
		$main .= '<p><strong>HTTP Server Port:</strong> ' . getenv('PTS_WEB_PORT') . '<br /><strong>WebSocket Server Port:</strong> ' . getenv('PTS_WEBSOCKET_PORT') . '<br /><strong>Phoromatic Server Path:</strong> ' . phoromatic_server::phoromatic_path() . '</p>';

		$main .= '<hr /><h2>Accounts</h2>';
		$stmt = phoromatic_server::$db->prepare('SELECT * FROM phoromatic_users ORDER BY AccountID,AdminLevel ASC');
		$result = $stmt->execute();
		$row = $result->fetchArray();

		$plevel = -1;
		while($row = $result->fetchArray())
		{
			switch($row['AdminLevel'])
			{
				case 0:
					$level = 'Disabled';
					$offset = null;
					break;
				case 1:
					$level = 'Main Administrator';
					$offset = null;
					break;
				case 2:
					$level = 'Administrator';
					$offset = str_repeat('-', 10);
					break;
				case 3:
					$level = 'Power User';
					$offset = str_repeat('-', 20);
					break;
				case 10:
					$level = 'Viewer';
					$offset = str_repeat('-', 30);
					break;
			}

			if($row['AdminLevel'] == 1)
			{
				if($plevel != -1)
					$main .= '</p>';
				$main .= '<p>';
			}

			$main .= $offset . ' <strong>' . $row['UserName'] . '</strong> (<em>' . $level . '</em>) <strong>Created On:</strong> ' . $row['CreatedOn'] . ' <strong>Last Log-In:</strong> ' . ($row['LastLogin'] != null ? $row['LastLogin'] : 'N/A') . '<br />';
			$plevel = $row['AdminLevel'];
		}
		if($plevel != -1)
			$main .= '</p>';

		$main .= '<hr /><h2>Phoromatic Server Log</h2>';
		$main .= '<p><textarea style="width: 80%; height: 400px;">' . file_get_contents(getenv('PTS_PHOROMATIC_LOG_LOCATION'))  . '</textarea></p>';

		echo phoromatic_webui_header_logged_in();
		echo phoromatic_webui_main($main, phoromatic_webui_right_panel_logged_in());
		echo phoromatic_webui_footer();
	}
}

?>