<?
/*============================================================================*\
| Projekt: FTP
|
| File: ftp.cls.php
| Version 0.2
| Autor: Toppi
| http://www.kacke.de
| Letze: 27.03.2008
|
| Voraussetungen PHP 5.1.x, socket enable, stream
|
| Beschreibung:
|
| FTP-Abstracktions-Class
|
|     -FTP
|     -SSL
|     -TLS
|     -SSLv2, v3, v23
|
| Supports FXP-Flie-transfer between two differnt servers
|
| NOTES:
|
| forgett my poor english, if you can
|
| please ....
| if you like my work, respect me and dont remove my Notes :-)
|
|  Copyright (C) 2001-2008 by Toppi (an@kacke.de)
|
|  This program is free software
|  you can redistribute it and/or modify
|  it under the terms of the GNU General Public License as published by
|  the Free Software Foundation; either version 2, or (at your option)
|  any later version.
|
|  This program is distributed in the hope that it will be useful,
|  but WITHOUT ANY WARRANTY; without even the implied warranty of
|  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
|  GNU General Public License for more details.
|
|  You should have received a copy of the GNU General Public License
|  along with this program; if not, write to the Free Software
|  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.


in memory of DoD

\*============================================================================*/

 
class Ftp {

    public $debug;
    public  $debugsock;
    public  $debugfunct;
    public $umask;
    public $timeout;
    public $name;
    public  $curr_path;
    public $last_action;
    private $_sock;
    private $_resp;
    private $opener;
    private $systype;
    private $connected;
    private $wrapper;
    private $protection_type;

    public function __construct () {

        $this->opener  = False;
        $this->debug   = False;
        $this->umask   = 0022;
        $this->timeout = 30;
        $this->last_action = time();
        $this->connected  = True;
        $this->wrapper = "FTP";
        $this->protection_type = "N";
        $this->_resp = "";

        if (@$_SERVER['SCRIPT_NAME']){
            $this->opener = True;
        }
    }

    public function __destruct() {
        return true;
    }


    public function ftp_connect($server, $port = 21, $wrapper="FTP", $protect_type=""){

        if(!$this->set_wrapper($wrapper)){
            return false;
        }

        switch ($this->wrapper){
            //Only SSL is different here. TLS etc. are set after connect
            case "SSL":
                $server = "ssl://".$server;
                break;
            default:
                $this->protection_type = strtoupper($protect_type);
        }

        $this->_debug_print("Trying to ".$server.":".$port." ...\n");
        $this->_sock = @fsockopen($server, $port, $errno, $errstr, $this->timeout);

        if (!$this->_sock || !$this->_ok()) {
            $this->_debug_print("Error : Cannot connect to remote host \"".$server.":".$port."\"\n");
            $this->_debug_print("Error : fsockopen() ".$errstr." (".$errno.")\n");
            return False;
        }
        socket_set_blocking ($this->_sock, False);
        $this->_debug_print("Connected to remote host \"".$server.":".$port."\"\n");
        return True;
    }

    public function ftp_login($user, $pass){

        switch ($this->wrapper) {
            case "FTP":
            case "SSL":
                break;
            default:

                $this->ftp_putcmd("AUTH TLS");
                if (!$this->_ok()) {
                    $this->_debug_print("Error : AUTH command failed\n");
                    return False;
                }
                stream_set_blocking ($this->_sock, true);
                stream_socket_enable_crypto($this->_sock, true, $this->wrapper);
        }

        $this->ftp_putcmd("USER", $user);
        if (!$this->_ok()) {
            $this->_debug_print("Error : USER command failed\n");
            return False;
        }


        $this->ftp_putcmd("PASS", $pass, True);
        if (!$this->_ok()) {
            $this->_debug_print("Error : PASS command failed\n");
            return False;
        }
        $this->_debug_print("Authentication succeeded\n");

        $this->ftp_systype();

        $this->ftp_type();

        $this->set_protection_type();

        stream_set_blocking ($this->_sock, false);

        return True;
    }

    public function ftp_pwd($hidden=false)    {

        $this->ftp_putcmd("PWD","",false,$hidden);
        if (!$this->_ok()) {
            $this->_debug_print("Error : PWD command failed\n");
            return False;
        }
        $this->curr_path =  preg_replace("/^\d{3} \"(.+)\".+\r{0,}\n{0,}/", "\\1", $this->_resp);
        return $this->curr_path;
    }

    public function ftp_size($pathname){

        $this->ftp_putcmd("SIZE", $pathname);
        if (!$this->_ok()) {
            $this->_debug_print("Error : SIZE command failed\n");
            return False;
        }

        return ereg_replace("^[0-9]{3} ([0-9]+)\r\n", "\\1", $this->_resp);
    }

    public function ftp_mdtm($pathname){

        $this->ftp_putcmd("MDTM", $pathname);
        if (!$this->_ok()) {
            $this->_debug_print("Error : MDTM command failed\n");
            return False;
        }
        $mdtm = ereg_replace("^[0-9]{3} ([0-9]+)\r\n", "\\1", $this->_resp);
        $date = sscanf($mdtm, "%4d%2d%2d%2d%2d%2d");
        $timestamp = mktime($date[3], $date[4], $date[5], $date[1], $date[2], $date[0]);

        return $timestamp;
    }

    public function ftp_systype(){

        $this->ftp_putcmd("SYST");
        if (!$this->_ok()) {
            $this->_debug_print("Error : SYST command failed\n");
            return False;
        }
        $DATA = explode(" ", $this->_resp);
        $this->systype = "UNIX";
        if(stristr(strtoupper($DATA[1]),"WIN")){
            $this->systype = "WINDOWS";
        }
        return $this->systype;
    }

    public function ftp_cdup() {

        $this->ftp_putcmd("CDUP");
        $response = $this->_ok();
        if (!$response) {
            $this->_debug_print("Error : CDUP command failed\n");
            return False;
        }
        $this->curr_path = $this->ftp_pwd(true);
        return $this->curr_path;
    }

    public function ftp_chdir($pathname){

        if(!$pathname){
            $pathname=$this->curr_path;
        }

        $this->ftp_putcmd("CWD", $pathname);
        $response = $this->_ok();
        if (!$response) {
            $this->_debug_print("Error : CWD command failed\n");
            return False;
        }
        $this->curr_path = $this->ftp_pwd(true);
        return $this->curr_path;
    }

    public function ftp_delete($pathname) {

        $this->ftp_putcmd("DELE", $pathname);
        $response = $this->_ok();
        if (!$response) {
            $this->_debug_print("Error : DELE command failed\n");
            return False;
        }
        return $response;
    }

    public function ftp_rmdir($pathname){

        $this->ftp_putcmd("RMD", $pathname);
        $response = $this->_ok();
        if (!$response) {
            $this->_debug_print("Error : RMD command failed\n");
            return False;
        }
        return True;
    }

    public function ftp_mkdir($pathname){

        $this->ftp_putcmd("MKD", $pathname);
        $response = $this->_ok();
        if (!$response) {
            $this->_debug_print("Error : MKD command failed\n");
            return False;
        }
        return True;
    }

    public function ftp_file_exists($pathname)    {

        if (!($remote_list = $this->ftp_nlist("-a"))) {
            $this->_debug_print("Error : Cannot get remote file list\n");
            return False;
        }

        reset($remote_list);
        while (list(,$value) = each($remote_list)) {
            if ($value == $pathname) {
                $this->_debug_print("Remote file ".$pathname." exists\n");
                return True;
            }
        }
        $this->_debug_print("Remote file ".$pathname." does not exist\n");
        return Null;
    }

    public function ftp_rename($from, $to)    {

        $this->ftp_putcmd("RNFR", $from);
        if (!$this->_ok()) {
            $this->_debug_print("Error : RNFR command failed\n");
            return False;
        }
        $this->ftp_putcmd("RNTO", $to);

        $response = $this->_ok();
        if (!$response) {
            $this->_debug_print("Error : RNTO command failed\n");
            return False;
        }
        return True;
    }

    public function ftp_nlist($arg = "", $pathname = ""){

        if (!($string = $this->ftp_pasv())) {
            return False;
        }

        if(!$pathname){
            $pathname=$this->curr_path;
        }

        if ($arg == "") {
            $nlst = "NLST";
        } else {
            $nlst = "NLST ".$arg;
        }
        $this->ftp_putcmd($nlst, $pathname);

        $sock_data = $this->ftp_open_data_connection($string);
        if (!$sock_data || !$this->_ok()) {
            $this->_debug_print("Error : Cannot connect to remote host\n");
            $this->_debug_print("Error : NLST command failed\n");
            return False;
        }
        $this->_debug_print("Connected to remote host\n");

        while (!feof($sock_data)) {
            $res = "";
            $res = ereg_replace("[\r\n]", "", fgets($sock_data, 512));
            if($res){$list[] = $res;}
        }

        $this->ftp_close_data_connection($sock_data);

        if (!$this->_ok()) {
            $this->_debug_print("Error : NLST command failed\n");
            return False;
        }

        return $list;
    }

    public function ftp_rawlist($pathname = "", $parsed=true){


        
        
        if (!($string = $this->ftp_pasv())) {
            return False;
        }
        $this->ftp_putcmd("LIST", $pathname);

        if(!$pathname){$pathname = $this->curr_path;}

        $sock_data = $this->ftp_open_data_connection($string);
        if (!$sock_data || !$this->_ok()) {
            $this->_debug_print("Error : Cannot connect to remote host\n");
            $this->_debug_print("Error : LIST command failed\n");
            return False;
        }

        $this->_debug_print("Connected to remote host\n");

        while (!feof($sock_data)) {
            $res = "";
            $res =  ereg_replace("[\r\n]", "", fgets($sock_data, 512));
            if($parsed){
                $list[] = $this->parse_rawlist($res);
            } else {
                $list[] = $res;
            }            
        }
        $this->ftp_close_data_connection($sock_data);

        if (!$this->_ok()) {
            $this->_debug_print("Error : LIST command failed\n");
            return False;
        }
        return $list;
    }

    public function ftp_get($localfile, $remotefile, $mode = 1){

        umask($this->umask);

        if (@file_exists($localfile)) {
            $this->_debug_print("Warning : local file will be overwritten\n");
        }

        $fp = @fopen($localfile, "w");
        if (!$fp) {
            $this->_debug_print("Error : Cannot create \"".$localfile."\"");
            $this->_debug_print("Error : GET command failed\n");
            return False;
        }

        if (!$this->ftp_type($mode)) {
            $this->_debug_print("Error : GET command failed\n");
            return False;
        }

        if (!($string = $this->ftp_pasv())) {
            $this->_debug_print("Error : GET command failed\n");
            return False;
        }

        $this->ftp_putcmd("RETR", $remotefile);

        $sock_data = $this->ftp_open_data_connection($string);
        if (!$sock_data || !$this->_ok()) {
            $this->_debug_print("Error : Cannot connect to remote host\n");
            $this->_debug_print("Error : GET command failed\n");
            return False;
        }
        $this->_debug_print("Connected to remote host\n");
        $this->_debug_print("Retrieving remote file \"".$remotefile."\" to local file \"".$localfile."\"\n");
        while (!feof($sock_data)) {
            fputs($fp, fread($sock_data, 4096));
        }
        fclose($fp);

        $this->ftp_close_data_connection($sock_data);

        $response = $this->_ok();
        if (!$response) {
            $this->_debug_print("Error : GET command failed\n");
            return False;
        }
        return True;
    }

    public function ftp_put($remotefile, $localfile, $mode = 1){

        if (!@file_exists($localfile)) {
            $this->_debug_print("Error : No such file or directory \"".$localfile."\"\n");
            $this->_debug_print("Error : PUT command failed\n");
            return False;
        }

        $fp = @fopen($localfile, "r");
        if (!$fp) {
            $this->_debug_print("Error : Cannot read file \"".$localfile."\"\n");
            $this->_debug_print("Error : PUT command failed\n");
            return False;
        }

        if (!$this->ftp_type($mode)) {
            $this->_debug_print("Error : PUT command failed\n");
            return False;
        }

        if (!($string = $this->ftp_pasv())) {
            $this->_debug_print("Error : PUT command failed\n");
            return False;
        }

        $this->ftp_putcmd("STOR", $remotefile);

        $sock_data = $this->ftp_open_data_connection($string);
        if (!$sock_data || !$this->_ok()) {
            $this->_debug_print("Error : Cannot connect to remote host\n");
            $this->_debug_print("Error : PUT command failed\n");
            return False;
        }
        $this->_debug_print("Connected to remote host\n");
        $this->_debug_print("Storing local file \"".$localfile."\" to remote file \"".$remotefile."\"\n");
        while (!feof($fp)) {
            fputs($sock_data, fread($fp, 4096));
        }
        fclose($fp);

        $this->ftp_close_data_connection($sock_data);

        $response = $this->_ok();
        if (!$response) {
            $this->_debug_print("Error : PUT command failed\n");
            return False;
        }
        return True;
    }


    public function fxp_send($remote_filearray_or_File, $to_ftpstream, $to_path){

        /*
        $to_ftpstream must be a already connected stream by this class
        if you have two objetcs initiated by this class, fill it
        */

        //check envs for this
        if(!is_array ($remote_filearray_or_File)){
            if (!strlen($remote_filearray_or_File)){
                $this->_debug_print("Error : Function ftp_fxp expects an array of files or a filenmame and its missing\n");
                return FALSE;
            }
            $temp_filearray  = $remote_filearray_or_File;
            $remote_filearray_or_File  = array();
            $remote_filearray_or_File[]=$temp_filearray;
        }
        $path_parts = pathinfo($remote_filearray_or_File[0]);
        if(!$path_parts["basename"]){
            $this->_debug_print("Error : Function fxp_send cant transfer directorys only! Filename or FileArray is missing\n");
            return false;
        }
        //check envs for target-stream
        if(!$to_ftpstream->ftp_pwd()){
            $this->_debug_print("Error : Target: $to_ftpstream is not a valid FTPsocket or its timed out\n");
            return FALSE;
        }
        //create destination rootdir
        if (!$to_ftpstream->ftp_chdir($to_path)){
            $to_ftpstream->ftp_chdir($to_ftpstream->ftp_root);
            $this->_debug_print("Error : Targetpath \"$to_path\" does not exists. Create needed Folders now\n");
            $a_pathes = preg_replace('/(^\/*)|(\/*$)/','',$to_path);
            $a_pathes = explode("/",$a_pathes);
            foreach($a_pathes as $newdir){
                if (!$to_ftpstream->ftp_chdir($newdir)){
                    if (!$to_ftpstream->ftp_mkdir($newdir)){
                        $this->_debug_print("Error : MKDIR \"$to_ftpstream->ftp_pwd()/$newdir/\"\n");
                        return false;
                    }
                }
            }
        }

        //  For server-to-server transfers, you would first issue a PASV command to the
        //  other server, take the address and port from the response and send it to the
        //  first server on a PORT command. Then you would issue a STOR on the target
        //  machine and a RETR on the source machine.

        foreach($remote_filearray_or_File as  $remotefile){


            $path_parts = pathinfo($remotefile);
            if (!$this->ftp_type(1)) {
                $this->_debug_print("Error :  Source Server! Cant swap into ASCII mode\n");
                return FALSE;
            }
            if (!$to_ftpstream->ftp_type(1)) {
                $this->_debug_print("Error : Destination Server! Cant swap into ASCII mode\n");
                return FALSE;
            }
            if (!($string = $to_ftpstream->ftp_pasv())) {
                $this->_debug_print("Error : Destination Server doesnt support PASV mode\n");
                return FALSE;
            }
            if(!$this->ftp_port($string) ){
                $this->_debug_print("Error : PORT command failed\n");
            }
            $this->ftp_putcmd("RETR", $remotefile);
            if(!$this->_ok() ){
                $this->_debug_print("Error :  RETR command failed\n");
            }
            //get size from reply
            //150 Opening BINARY mode data connection
            $size = preg_replace('/.*(?=\()|(\W)|([a-z])|(\40)/smi','',$this->_resp);

            $to_ftpstream->ftp_putcmd("STOR", $path_parts["basename"]);
            if(!$to_ftpstream->_ok() ){
                $this->_debug_print("Error : STOR command failed\n");
            }
            //226 Transfer complete
            $this->_ok();
            $to_ftpstream->_ok();
            if(preg_match('/^226/',$this->_resp) && preg_match('/^226/',$to_ftpstream->_resp) ){
                $sum_files++;
                $sum_size+=$size;
            }
            $this->_debug_print("Transferred file \"$remotefile\" to \"$to_path/$path_parts[basename]\" ($size Bytes)\n");
        }
        //queue done go back into idle
        if (!$this->ftp_type(0)) {
            $this->_debug_print("Error : Source Server! Cant swap into BINARY mode\n");
        }
        if (!$this->ftp_pasv()) {
            $this->_debug_print("Error : Source Server cant go back into PASV mode\n");
        }
        if (!$to_ftpstream->ftp_type(0)) {
            $this->_debug_print("Error : Source Server! Cant swap into BINARY mode\n");
        }
        if (!($to_ftpstream->ftp_pasv())) {
            $this->_debug_print("Error : Destination Server cant go back into PASV mode\n");
        }
        $this->_debug_print("·-------------------[Totals]-\n");
        $this->_debug_print("| Transferred $sum_files Files in ".number_format($sum_size,0,",",".")." Bytes Total\n");
        $this->_debug_print("·-------------------------------------\n");

        return true;

    }

    public function ftp_site($command)    {
        $this->ftp_putcmd("SITE", $command);
        $response = $this->_ok();
        if (!$response) {
            $this->_debug_print("Error : SITE command failed\n");
        }
        return $response;
    }

    public function ftp_noob () {
        $this->ftp_putcmd("CWD", ".",False,True);
        if (!$this->_ok()) {
            $this->_sock = False;
            return False;
        }
        return True;
    }

    public function ftp_quit()
    {
        $this->ftp_putcmd("QUIT");
        if (!$this->_ok() || !fclose($this->_sock)) {
            $this->_debug_print("Error : QUIT command failed\n");
            return False;
        }
        $this->_debug_print("Disconnected from remote host\n");
        $this->_sock = False;
        return True;
    }


    public function ftp_type($mode="")    {
        if ($mode) {
            $type = "I"; //Binary mode
        } else {
            $type = "A"; //ASCII mode
        }
        $this->ftp_putcmd("TYPE", $type);
        $response = $this->_ok();
        if (!$response) {
            $this->_debug_print("Error : TYPE command failed\n");
            return False;
        }
        return True;
    }

    private function ftp_port($ip_port)    {

        $this->ftp_putcmd("PORT", $ip_port);
        $response = $this->_ok();
        if (!$response) {
            $this->_debug_print("Error : PORT command failed\n");
            return False;
        }
        return True;
    }

    private function ftp_pasv()    {

        $this->ftp_putcmd("PASV");
        if (!$this->_ok()) {
            $this->_debug_print("Error : PASV command failed\n");
            return False;
        }
        $ip_port = ereg_replace("^.+ \\(?([0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]+,[0-9]+)\\)?.*\r\n$", "\\1", $this->_resp);
        return $ip_port;
    }

    private function ftp_putcmd($cmd, $arg = "", $password = False, $hidden = False)
    {
        if ($arg != "") {
            $put = $cmd." ".$arg;
        }else{
            $put = $cmd;
        }
        $put =  str_replace("\r", '', $put);
        $put =  str_replace("\n", '', $put);

        if(!fputs($this->_sock, $put."\n")){
            return False;
        }
        if($password){
            $this->_debug_print("> ".$cmd." *******\n");
            return True;
        }
        if($hidden){
            return True;
        }
        $this->_debug_print(">".$put."\n");

        return True;
    }

    private function _ok($hidden = False){

        $this->_resp = "";
        $res         = "";
        $timer          = 0;
        if(!$this->is_Connected()){
            return False;
        }
        while (True) {
            if(False === ($res = @fgets($this->_sock, 1024))){
                $timer++;
                usleep(100000);
                if($timer > 100){
                    $this->_debug_print("Error: Socket timed out");
                    return $this->connected = False;
                }
                continue;
            }
            if(!$hidden){
                $this->_debug_print($res);
            }
            $this->_resp .= $res;
            if(is_numeric(substr($res,0,3)) && substr($res,3,1) == " "){
                break;
            }
        }
        $this->last_action = time();
        if (!ereg("^[123]", $this->_resp)) {
            return False;
        }
        return True;
    }

    public function is_Connected() {
        return $this->connected;
    }

    private function set_wrapper($wrapper){

        $wrapper = strtoupper($wrapper);

        $known_wraps = array(
        "FTP"=>"FTP",
        "SSL"=>"SSL",
        "TLS"=>STREAM_CRYPTO_METHOD_TLS_CLIENT,
        "SSLV2"=>STREAM_CRYPTO_METHOD_SSLv2_CLIENT,
        "SSLV3"=>STREAM_CRYPTO_METHOD_SSLv3_CLIENT,
        "SSLV23"=>STREAM_CRYPTO_METHOD_SSLv23_CLIENT);

        if($this->wrapper = $known_wraps[$wrapper]){
            return true;
        }
        $prods = implode($known_wraps,", ");
        $this->_debug_print("Error : Unknown Protocoll \"$wrapper\". Supportetd are: $prods \n");
        return false;
    }

    private function set_protection_type(){
        //
        if($this->protection_type == "N"){
            return true;
        }
        $this->ftp_putcmd("PROT ".$this->protection_type);
        if (!$this->_ok()) {
            $this->_debug_print("Error : Protection command failed\n");
            return False;
        }
        return true;
    }


    private function ftp_close_data_connection($sock)
    {
        $this->_debug_print("Disconnected from remote host\n");
        return fclose($sock);
    }

    private function ftp_open_data_connection($ip_port) {

        if (!ereg("[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]+,[0-9]+", $ip_port)) {
            $this->_debug_print("Error : Illegal ip-port format(".$ip_port.")\n");
            return False;
        }

        $DATA = explode(",", $ip_port);
        $ipaddr = $DATA[0].".".$DATA[1].".".$DATA[2].".".$DATA[3];
        $port   = $DATA[4]*256 + $DATA[5];
        $this->_debug_print("Trying to ".$ipaddr.":".$port." ...\n");

        $data_connection = @fsockopen($ipaddr, $port, $errno, $errstr);

        switch ($this->wrapper){
            case "FTP":
            case "SSL":
                break;
            default:
                stream_set_blocking ($data_connection, true);
                stream_socket_enable_crypto($data_connection, true, $this->wrapper);
        }

        if (!$data_connection) {
            $this->_debug_print("Error : Cannot open data connection to ".$ipaddr.":".$port."\n");
            $this->_debug_print("Error : ".$errstr." (".$errno.")\n");
            return False;
        }

        return $data_connection;
    }

    private function parse_rawlist($list) {

        if(preg_match("/([-dl][rwxstST-]+).* ([0-9]*) ([a-zA-Z0-9]+).* ([a-zA-Z0-9]+).* ([0-9]*) ([a-zA-Z]+[0-9: ]*[0-9])[ ]+(([0-9]{2}:[0-9]{2})|[0-9]{4}) (.+)/i", $list, $regs)) {

            $type = (int) strpos("-dl", $regs[1]{0});
            $tmp_array['line'] = $regs[0];
            $tmp_array['type'] = $type;
            $tmp_array['rights'] = $regs[1];
            $tmp_array['number'] = $regs[2];
            $tmp_array['user'] = $regs[3];
            $tmp_array['group'] = $regs[4];
            $tmp_array['size'] = $regs[5];
            $tmp_array['date'] = date("m-d",strtotime($regs[6]));
            $tmp_array['time'] = $regs[7];
            $tmp_array['name'] = $regs[9];
            
            return $tmp_array;

        }
    }

    private function _debug_print($message){

        $args = func_get_args();

        if($this->debugsock){
            $message = ereg_replace("[\r\n]", "",$message);
            socket_write($this->debugsock, $message."\n");
            return;
        }

        if($this->debugfunct){
            if (function_exists($this->debugfunct)){
                call_user_func_array($this->debugfunct, $args);
            }
            return;
        }

        if ($this->opener){
            $message= nl2br($message);
        }

        if(!$this->debug){return;}
        echo $message;
        return;
    }
}
?>