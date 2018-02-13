#!/usr/bin/env php
<?php
/*GitTest*/
require_once('./websockets.php');

class CMDServer extends WebSocketServer {
  //protected $maxBufferSize = 1048576; //1MB... overkill for an echo server, but potentially plausible for other applications.
  protected $tcs_users                            = array();

  protected function printClientList() {
		echo "\n\nCLIENT LIST\n{\n";
		$i = 0;
		foreach ($this->users as $user) {
			echo "      [".$user->tcs_id."]  : ".$user->ip."\n";
			$i++;
		}
    //var_dump($this->users);
    /*
    echo "\n\nTCS CLIENT LIST\n{\n";
      $i = 0;
    foreach ($this->tcs_users as $user) {
			echo "      [".$user->tcs_id."]  : ".$user->ip."\n";
			$i++;
		}
    */
		echo "\n}\n\n";
	}

  protected function process ($from, $data) { // changed $user to $from, just to avoid confusion
	//foreach ($this->users as $user){ // sending to all connected users
		//if($from !== $user){ // avoid to send msg to sender
			//$this->send($user, $data);
		//}
	//}
	echo "RECV : ".$data."\n";

	if(preg_match("/CLIENT/",$data)){
		echo "NEW TCS CLIENT : ".$data."\n";
		$ndxStr = preg_replace("/CLIENT/", "", $data);
		if(empty($ndxStr)){
			echo "CLIENT FALSE DATA\n";
			return;
		}
		$ndx = intval($ndxStr);
        $from->tcs_id = $ndx;
        $this->printClientList();
		
        foreach ($this->users as $user) {
          try{
            if($user == $from){
              $msg = "ALL#ACCEPTED#".$ndx."#NOTICE#EOF";
              $this->send($from, $msg);
            }else{
              $msg = "ALL#CONNECTED#".$ndx."#NOTICE#EOF";
              $this->send($user, $msg);
            }
          }catch(Exception $e){
            unset($user);
            $this->printClientList();
          }
        }

		}else{
			if(strlen($data) > 0 ){
      //  echo "??????? : ".$data."\n";
				//if the message comes from a client, let the message send to a server.
				if(array_search($from,$this->users)){
					//echo "TO SERVER\n";
					$ndx = substr($data,0,strpos($data,"#"));

					if($ndx === "ALL"){
						foreach ($this->users as $user) {
							try{
								$this->send($user, $data);
							}catch(Exception $e){
								unset($user);
								$this->printClientList();
							}
						}
					}else{
						foreach ($this->users as $user) {
							if($user->tcs_id == $ndx){
								try{
									$this->send($user, $data);
								}catch(Exception $e){
									unset($user);
									$this->printClientList();
								}
							}
						}
					}
				}


			}
		}
  }

  protected function connected ($user) {
    // Do nothing: This is just an echo server, there's no need to track the user.
    // However, if we did care about the users, we would probably have a cookie to
    // parse at this step, would be looking them up in permanent storage, etc.

  }

  protected function closed ($user) {
    // Do nothing: This is where cleanup would go, in case the user had any sort of
    // open files or other objects associated with them.  This runs after the socket
    // has been closed, so there is no need to clean up the socket itself here.
    	$tcs_id = $user->tcs_id;
    	if(array_search($user,$this->users)){
    		unset($user);
    	}
    	foreach ($this->users as $user) {
    				try{
    					$msg = "ALL#DISCONNECTED#".$tcs_id."#NOTICE#EOF";
    					$this->send($user, $msg);

    				}catch(Exception $e){
    					unset($user);
    				}
    			}
    	$this->printClientList();
      }
    }

    $echo = new CMDServer("0.0.0.0","9000");

try {
  $echo->run();
}
catch (Exception $e) {
  $echo->stdout($e->getMessage());
}
