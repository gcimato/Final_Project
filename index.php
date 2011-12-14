<?php
session_start();
$className = 'index';
if(!empty($_GET)) {
	$className = key($_GET);
} 

$obj = new $className();

abstract class mongo_data {
	
	protected $db;
	protected $collection;
	protected $cursor;
	protected $record_id;
	protected $temp;
	protected $record;
	protected $state;
	
	protected function mconnect() {
		$username = 'kwilliams';
		$password = 'mongo1234';
		$this->connection = new Mongo("mongodb://${username}:${password}@localhost/test",array("persist" => "x"));
		$this->setDb();
	}
	protected function setDb($db = 'gc84') {
		$this->db = $this->connection->$db;
	}
	protected function setCollection($collection) {
		$this->collection = $this->db->$collection;
		
	}
	protected function findRecords($query = null) {
		if($query == null) {
			$this->cursor = $this->collection->find();
		} else {
			$this->cursor = $this->collection->find($query);
		}
		return $this->cursor;
	}
	
	protected function findRecord($query = null) {
		if($query == null) {
			$this->record = $this->collection->findOne();
		} else {
			$this->record = $this->collection->findOne($query);
		}
		return $this->record;
	}
	
	protected function add($query) {
		$this->collection->insert($query);
		$this->record_id = $query;
		$this->cursor = $this->collection->find();
		
	}
	
	protected function getRecord() {
		foreach($this->record as $key => $value) {
				
				$this->temp .= $key . ': ' . $value . "<br>\n";
				
			}		
			$this->temp .= '<hr>';
		return $this->temp;
	}

	protected function getState() {
		foreach($this->record as $key => $value) {
			if ($key == 'state')
			{
				$this->state .= strtoupper($value);
			}		
		}		
		return $this->state;
	}
	
	
	protected function update($query) {
		$this->collection->update($query);
	}
	protected function delete($query) {
		
	}
	protected function getRecords() {
			
		foreach($this->cursor as $record) {
			foreach($record as $key => $value) {
				
				$this->temp .= $key . ': ' . $value . "<br>\n";
				
			}		
			$this->temp .= '<hr>';
		}
		return $this->temp;
	}
 	protected function getRecordID() {
 		return $this->record_id;
 	}
}
abstract class data extends mongo_data {
	protected $query;
	protected $connection;
}
abstract class request extends data {
	protected $data;
	protected $form;
	 function __construct() {
	 	
		if($_SERVER['REQUEST_METHOD'] == 'GET') {
			$this->get();

		} else {
			
			$this->post();
		}
		$this->display();
	}
	protected function get() {
		// gets the first value of the $_GET array, so that the correct form function is called.
		$function = array_shift($_GET) . '_get';
		$this->$function();
	}
	protected function post() {
		// gets the first value of the $_GET array, so that the correct form function is called.
		$function = array_shift($_GET) . '_post';
		$this->$function();
	}
}
//this is the class for the homepage

abstract class page extends request {
	protected $header;
	protected $menu;
	protected $content;
	protected $footer;
	
	protected function display() {
		echo $this->setHeader();
		echo $this->setMenu();
		echo $this->content;
		echo $this->setFooter();
	}

	protected function setHeader() {
		$this->header = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
						 "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
						 <html xmlns="http://www.w3.org/1999/xhtml">
						<head>
							<title>Conforming XHTML 1.1 Template</title>
							 <link rel="stylesheet" href="css/style.css">
						</head>
						<body>
						<div id="container">
						';
		return $this->header;
	}

	protected function setMenu() {
		$this->menu = '<div id="menuleft"></div>';
		$this->menu .= '<div id="menu">
							<div id="phptitle"><img src="images/php.png"</div>
						</div>';
	
		return $this->menu;
	
	}	
	
	protected function setFooter() {
		$this->footer = '</div>
						</body>
					    </html>';
		return $this->footer;
	
	}


}

class index extends page {
	function __construct() {
		parent::__construct();
	}

	protected function get() {
		$this->content = '<div id="content">';
		$this->content .= '<div id="contentLeft"></div>';
		$this->content .= '<div id="contentMid">';
		$this->content .= '<h1>Welcome To The App</h1>';
		$this->content .= '<a href="index.php?people=login">Click Here To Login</a><br>';
		$this->content .= '<a href="index.php?people=signup">Click Here To Signup</a><br>';
		$this->content .= '<a href="index.php?people=directory">Click Here To View Users</a><br>';
		$this->content .= '<a href="index.php?people=reset">Click Here To Reset Your Password</a><br>';
		$this->content .= '</div>';
		$this->content .= '<div id="contentRight"></div>';
		$this->content .= '</div>';
	}
}
//this will handle logins

class people extends page {
	function __construct() {
		$this->mconnect();
		$this->setCollection('people');
		parent::__construct();
	}

	protected function login_get() {
		$this->content = '<div id="content">';
		$this->content .= '<div id="contentLeft"></div>';
		$this->content .= '<div id="contentMid">';
		$this->content .= '<h1>Login Here</h1>';
		$this->content .= $this->login_form();
		$this->content .= '</div>';
		$this->content .= '<div id="contentRight"></div>';
		$this->content .= '</div>';
		
	}
	
	protected function login_form() {
		
		$this->form = '<FORM action="./index.php?people=login" method="post">
    				   <LABEL for="username">Username: </LABEL>
              		   <INPUT name="username" type="text" id="username"><br>
    		           <LABEL for="password">Password: </LABEL>
                       <INPUT name="password" type="password" id="password"><br>
                       <INPUT type="submit" value="Login"> <INPUT type="reset">
					   </br>
					   </br>
                       <a href="./index.php?people=signup">Click To Signup</a>
					   <br>
					   <br>
					   <a href="index.php">Click Here for Home Page</a>
 					   </FORM>';
		return $this->form;
	
	}
	protected function login_post() {
		
		$this->findRecord(array('username' => $_POST['username']));
		if ($_SESSION['username'] = $this->record['username'])
		{
			$this->findRecord(array('password' => $_POST['password']));
			if ($_SESSION['password'] = $this->record['password'])
			{
				$this->content = '<div id="content">';
				$this->content .= '<div id="contentLeft"></div>';
				$this->content .= '<div id="contentMid">';
				$this->content .= 'You are currently logged in as ';
				$this->content .= $_SESSION['username'];
				$this->content .= '<br><br><a href="index.php?people=user">Click Here To View Cities In Your State</a><br>';
				$this->content .= '</div>';
				$this->content .= '<div id="contentRight"></div>';
				$this->content .= '</div>';
			}
			
			else 
			{
			?>
			<script type="text/javascript">
				alert("The user <?php echo $_POST['username']; ?>  or password is invalid.");
				history.back();
			</script>
			
			<?php	
			}
		}
		else
		{
?>
			<script type="text/javascript">
				alert("The user <?php echo $_POST['username']; ?>  or password is invalid.");
				history.back();
			</script>
			
<?php		
		}
	}
	
	protected function signup_get() {
		$this->content = '<div id="content">';
		$this->content .= '<div id="contentLeft"></div>';
		$this->content .= '<div id="contentMid">';
		$this->content .= '<h1>Signup Here</h1>';
		$this->content .= $this->signup_form();
		$this->content .= '</div>';
		$this->content .= '<div id="contentRight"></div>';
		$this->content .= '</div>';
		
	}
	protected function signup_form() {
		$this->form = '<FORM action="./index.php?people=signup" method="post">
    				   <LABEL for="firstname">First name: </LABEL>
              		   <INPUT type="text" name="fname" id="firstname"><BR>
    				   <LABEL for="lastname">Last name: </LABEL>
              		   <INPUT type="text" name="lname" id="lastname"><BR>
    				   <LABEL for="email">Email: </LABEL>
              		   <INPUT type="text" name="email" id="email"><BR>
    				   <LABEL for="username">Username: </LABEL>
              		   <INPUT type="text" name="username" id="username"><BR>					   
					   <LABEL for="password">Password: </LABEL>
              		   <INPUT type="password" name="password" id="password"><BR>
					   <LABEL for="state">State: </LABEL>
              		   <INPUT type="text" name="state" id="state"><BR>						   
              		   <LABEL for="zip">Zip Code: </LABEL>
              		   <INPUT type="text" name="zip" id="zip"><BR>
              		   <INPUT type="submit" value="Send"> <INPUT type="reset">
    				   </P>
				   	   </FORM>
					   	<br>
					   <br>
					   <a href="index.php">Click Here for Home Page</a>
					   ';
		return $this->form;			  
	}
	
	protected function signup_post() {
			
	$this->findRecord(array('username' => $_POST['username']));
	if ($_POST['username'] != $this->record['username'])
		{
			$this->add($_POST);
			$this->getRecordID();
			$this->content = '<div id="content">';
			$this->content .= '<div id="contentLeft"></div>';
			$this->content .= '<div id="contentMid">';
			$this->content .= '<a href="index.php?people=login">Click Here To Login</a><br>';
			$this->content .= '<a href="index.php?people=directory">Click Here To View Users</a><br>';
			$this->content .= '<br><br><a href="index.php?people=user">Click Here To View Cities In Your State</a><br>';
			$this->content .= '</div>';
			$this->content .= '<div id="contentRight"></div>';
			$this->content .= '</div>';
		}
		else
		{
?>
			<script type="text/javascript">
				alert("The username <?php echo $_POST['username']; ?>  is already registerd.");
				history.back();
			</script>
			
<?php		
		}
	
	}
	
	protected function directory_get() {
		$this->content = '<div id="content">';
		$this->content .= '<div id="contentLeft"></div>';
		$this->content .= '<div id="contentMid">';	
		$this->content .= '<h1>User Accounts</h1>';
		$this->findRecords();
		$this->content .= $this->getRecords();
		$this->content .= '<br><br><a href="index.php">Click Here for Home Page</a>';
		$this->content .= '</div>';
		$this->content .= '<div id="contentRight"></div>';
		$this->content .= '</div>';
		
	}
	
	
	protected function user_get() {

		$this->findRecord(array('username' => $_SESSION['username']));
		
		$this->content = '<div id="content">';
		$this->content .= '<div id="contentLeft"></div>';
		$this->content .= '<div id="contentMid">';
		
		$this->content .= $this->getRecord();
	
		$this->content .= '<a href="index.php"> Click To Return to the Home Page</a><br><br>';
		
		foreach($this->getRequest()->response->LocationInfo->city as $city) {

			foreach($city->children() as $data)
						{
							$this->content .= $data->getName() . ": " . $data . "<br />";
						}
		$this->content .= "<br />";
		}
		
		$this->content .= '</div>';
		$this->content .= '<div id="contentRight"></div>';
		$this->content .= '</div>';
	}
	
	protected function getRequest() {
		$xml;
	
		$ch = curl_init($this->getURL());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
		$data = curl_exec($ch);
		curl_close($ch);
	
		$this->xml = new SimpleXmlElement($data, LIBXML_NOCDATA);
		
		return $this->xml;
					
	}
	
	protected function getURL() {
		$url;  
		
		$this->url = 'http://api.trulia.com/webservices.php?library=LocationInfo&function=getCitiesInState&state=';
		$this->url .= $this->getState();
		$this->url .= "&apikey=men3rcxkky6mrjcj3phvuwuk";
		return $this->url;
			
	}
	
	
	protected function reset_get() {
		$this->content = '<div id="content">';
		$this->content .= '<div id="contentLeft"></div>';
		$this->content .= '<div id="contentMid">';
		$this->content .= '<h1>Reset your password</h1>';
		$this->content .= $this->reset_form();
		$this->content .= '<br><br><a href="index.php"> Click To Return to the Home Page</a>';
		$this->content .= '</div>';
		$this->content .= '<div id="contentRight"></div>';
		$this->content .= '</div>';
	}
	
	protected function reset_post() {
				
			print_r($this->findRecord(array('username' => $_POST['username'])));
	}
	
	protected function reset_form() {
		$this->form = '<FORM action="./index.php?people=reset" method="post">
              		   <LABEL for="email">Email Address:</LABEL>
              		   <INPUT type="text" name="username" id="email"><BR>
    				   <INPUT type="submit" value="Send My Password">
    				   </P>
				   	   </FORM>';
		return $this->form;	

	}

}

class service extends page {
	function __construct() {
		$this->mconnect();
		$this->setCollection('states');
		parent::__construct();
}
	
	protected function city_get() {
		
		$this->content = '<form action="index.php?service=city" method="post">
  State: <input type="text" name="state" /><br />
  <input type="submit" value="Submit" />
</form>';
	
	}
	
	protected function city_post() {
		$this->add($_POST);
		$this->content .= $this->getRecords();
		
		
	
	}

}


	class somepage extends page {
	
		function thispage_get(){
			echo "this page is here";
		}
	
	}

?>