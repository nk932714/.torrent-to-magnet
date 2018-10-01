<?php
$sourcea = $_GET["link"];

$magnet_mask = 'magnet:?xt=urn:btih:';


$content = file_get_contents($sourcea);
$content_d = bdecode($content);

# Check if bdecode succeeded
if(empty($content_d)) exit('Something is wrong with the torrent. BDecode failed.');

# Calculate info_hash
//$info_hash = sha1(bencode($content_d['info']), true); //true gives encoded output which is not readable by browser
$info_hash = sha1(bencode($content_d['info']), false);

# Calculate length
$length = 0;
function add_length($value, $key)
{
    global $length;
    if($key == 'length') $length += $value;
}
array_walk_recursive($content_d, 'add_length');

//echo $length;
//print_r($info_hash);

$full_magnet_link = $magnet_mask.$info_hash;
echo $full_magnet_link;



























function bdecode($str) {
    $pos = 0;
    return bdecode_r($str, $pos);
}

function bdecode_r($str, &$pos) {
    $strlen = strlen($str);
    if (($pos < 0) || ($pos >= $strlen)) {
            return null;
    }
    else if ($str{$pos} == 'i') {
            $pos++;
            $numlen = strspn($str, '-0123456789', $pos);
            $spos = $pos;
            $pos += $numlen;
            if (($pos >= $strlen) || ($str{$pos} != 'e')) {
                    return null;
            }
            else {
                    $pos++;
                    return intval(substr($str, $spos, $numlen));
            }
    }
    else if ($str{$pos} == 'd') {
            $pos++;
            $ret = array();
            while ($pos < $strlen) {
                    if ($str{$pos} == 'e') {
                            $pos++;
                            return $ret;
                    }
                    else {
                            $key = bdecode_r($str, $pos);
                            if ($key == null) {
                                    return null;
                            }
                            else {
                                    $val = bdecode_r($str, $pos);
                                    if ($val == null) {
                                            return null;
                                    }
                                    else if (!is_array($key)) {
                                            $ret[$key] = $val;
                                    }
                            }
                    }
            }
            return null;
    }
    else if ($str{$pos} == 'l') {
            $pos++;
            $ret = array();
            while ($pos < $strlen) {
                    if ($str{$pos} == 'e') {
                            $pos++;
                            return $ret;
                    }
                    else {
                            $val = bdecode_r($str, $pos);
                            if ($val == null) {
                                    return null;
                            }
                            else {
                                    $ret[] = $val;
                            }
                    }
            }
            return null;
    }
    else {
            $numlen = strspn($str, '0123456789', $pos);
            $spos = $pos;
            $pos += $numlen;
            if (($pos >= $strlen) || ($str{$pos} != ':')) {
                    return null;
            }
            else {
                    $vallen = intval(substr($str, $spos, $numlen));
                    $pos++;
                    $val = substr($str, $pos, $vallen);
                    if (strlen($val) != $vallen) {
                            return null;
                    }
                    else {
                            $pos += $vallen;
                            return $val;
                    }
            }
    }
}

function bencode($var) {
    if (is_int($var)) {
            return 'i' . $var . 'e';
    }
    else if (is_array($var)) {
            if (count($var) == 0) {
                    return 'de';
            }
            else {
                    $assoc = false;
                    foreach ($var as $key => $val) {
                            if (!is_int($key)) {
                                    $assoc = true;
                                    break;
                            }
                    }
                    if ($assoc) {
                            ksort($var, SORT_REGULAR);
                            $ret = 'd';
                            foreach ($var as $key => $val) {
                                    $ret .= bencode($key) . bencode($val);
                            }
                            return $ret . 'e';
                    }
                    else {
                            $ret = 'l';
                            foreach ($var as $val) {
                                    $ret .= bencode($val);
                            }
                            return $ret . 'e';
                    }
            }
    }
    else {
            return strlen($var) . ':' . $var;
    }
}

