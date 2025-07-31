<?php

header("Content-Type: application/json; charset=UTF-8");
$matp = isset($_POST["matp"]) ? htmlspecialchars($_POST["matp"]) : "";
$maqh = isset($_POST["maqh"]) ? intval($_POST["maqh"]) : "";
$result = ["success" => false];
if ($matp) {
	include "cities/quan_huyen.php";
	$quan = ntco_search_in_array($quan_huyen, "matp", $matp);
	usort($quan, "ntco_natorder");
	if ($quan) {
		$result = ["success" => true, "data" => $quan];
	}
}
if ($maqh) {
	include "cities/xa_phuong_thitran.php";
	$id_xa = sprintf("%05d", intval($maqh));
	$xa = ntco_search_in_array($xa_phuong_thitran, "maqh", $id_xa);
	usort($xa, "ntco_natorder");
	if ($xa) {
		$result = ["success" => true, "data" => $xa];
	}
}
echo json_encode($result);
exit;
function is_serialized($data, $strict = true) {
	if (!is_string($data)) {
		return false;
	}
	$data = trim($data);
	if ("N;" == $data) {
		return true;
	}
	if (strlen($data) < 4) {
		return false;
	}
	if (":" !== $data[1]) {
		return false;
	}
	if ($strict) {
		$lastc = mb_substr($data, -1);
		if (";" !== $lastc && "}" !== $lastc) {
			return false;
		}
	} else {
		$semicolon = strpos($data, ";");
		$brace = strpos($data, "}");
		if (false === $semicolon && false === $brace) {
			return false;
		}
		if (false !== $semicolon && $semicolon < 3) {
			return false;
		}
		if (false !== $brace && $brace < 4) {
			return false;
		}
	}
	$token = $data[0];
	switch ($token) {
		case "s":
			if ($strict) {
				if ("\"" !== mb_substr($data, -2, 1)) {
					return false;
				}
			} else {
				if (false === strpos($data, "\"")) {
					return false;
				}
			}
			break;
		case "a":
		case "O":
			return (bool) preg_match("/^" . $token . ":[0-9]+:/s", $data);
		case "b":
		case "i":
		case "d":
			$end = $strict ? "\$" : "";
			return (bool) preg_match("/^" . $token . ":[0-9.E+-]+;" . $end . "/", $data);
		default:
			return false;
	}
}
function maybe_unserialize($original) {
	if (is_serialized($original)) {
		return @unserialize($original);
	}
	return $original;
}
function unused_column($array) {
	unset($array["vt"]);
	unset($array["ghn"]);
	unset($array["vnpost"]);
	unset($array["vnpostv2"]);
	return $array;
}
function ntco_search_in_array($array, $key, $value) {
	$results = [];
	if (is_array($array)) {
		if (isset($array[$key]) && $array[$key] == $value) {
			$results[] = unused_column($array);
		} else {
			if (isset($array[$key]) && is_serialized($array[$key]) && in_array($value, maybe_unserialize($array[$key]))) {
				$results[] = unused_column($array);
			}
		}
		foreach ($array as $subarray) {
			$results = array_merge($results, ntco_search_in_array($subarray, $key, $value));
		}
	}
	return $results;
}
function ntco_natorder($a, $b) {
	return strnatcasecmp($a["name"], $b["name"]);
}
