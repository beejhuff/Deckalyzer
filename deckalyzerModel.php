<?php
	/**
	 *
	 */
	class deckalyzerModel{
		private $error = '';
		private $mysqli;
		private $user;

		public function __construct(){
			$this->connectToDB();
		}

		public function __destruct(){
			if ($this->mysqli){
				$this->mysqli->close();
			}
		}

		private function connectToDB(){
			require('DBCredentials.php');
			$this->mysqli = new mysqli($servername, $username, $password, $dbname);
			if ($this->mysqli->connect_error) {
				$this->error = $mysqli->connect_error;
			}
		}

		public function getUser() {
			return $this->user;
		}

		public function loadUser($userId){
			$user = new user();
			if($user->load($userId)){
				$this->user = $user;
				return array(true, "");
			} else {
				$this->user = null;
				return array(false, "Could not select user");
			}
		}

		public function addCard($data){
			$this->error = '';

			// if(!$this->user){
			// 	$this->error = "No user specified. Could not add card";
			// 	return $this->error;
			// }

			$name = $data['name'];
			if(!$name){
				$this->error = "No card name specified. Could not add card";
				return $this->error;
			}

			$nameEscaped = $this->mysqli->real_escape_string($name);
			$userIDEscaped = $this->mysqli->real_escape_string(1);//Multi-user is a stretch feature. For now we will assume there is only one user.
			$forTradeEscaped = $this->mysqli->real_escape_string($data['forTrade']);
			$numOwnedEscaped = $this->mysqli->real_escape_string($data['numOwned']);


			$sql = "INSERT INTO cards (name, ownerId, forTrade, numOwned) VALUES ('$nameEscaped', '$userIDEscaped', '$forTradeEscaped', '$numOwnedEscaped')";

			if(!$result = $this->mysqli->query($sql)){
				$this->error = $this->mysqli->error;
			}

			return $this->error;
		}

		public function getCard($id){
			$this->error = '';
			$card = null;

			// if (!$this->user) {
			// 	$this->error = "User not specified. Unable to get card.";
			// 	return $this->error;
			// }

			if (!$this->mysqli) {
				$this->error = "No connection to database. Unable to retrieve card";
				return array($card, $this->error);
			}

			if (!$id) {
				$this->error = "No id specified. Unable to retrieve card.";
				return array($card, $this->error);
			}

			$idEscaped = $this->mysqli->real_escape_string($id);
			$userIDEscaped = $this->mysqli->real_escape_string(1); //HARDCODED FOR TESTING

			$sql = "SELECT * FROM cards WHERE ownerId = $userIDEscaped AND id = '$idEscaped'";
			if ($result = $this->mysqli->query($sql)) {
				if ($result->num_rows > 0) {
					$card = $result->fetch_assoc();
				}
				$result->close();
			} else {
				$this->error = $this->mysqli->error;
			}

			return array($card, $this->error);
		}

		public function getCardCollection(){
			$this->error = '';
			$cards = array();

			// if (!$this->user) {
			// 	$this->error = "User not specified. Unable to get cards.";
			// 	return $this->error;
			// }

			if (!$this->mysqli) {
				$this->error = "No connection to database. Could not get cards";
				return array($cards, $this->error);
			}

			$nameEscaped = $this->mysqli->real_escape_string($name);
			$userIDEscaped = $this->mysqli->real_escape_string(1);//THIS IS HARD CODED!!!!!!

			$sql = "SELECT * FROM cards WHERE ownerId = $userIDEscaped";
			if($result = $this->mysqli->query($sql)){
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()) {
						array_push($cards, $row);
					}
				}
				$result->close();
			} else {
				$this->error = $mysqli->error;
			}

			return array($cards, $this->error);
		}

		public function editCard($data){
			$this->error = '';

			// if (!$this->user) {
			// 	$this->error = "User not specified. Unable to update card.";
			// 	return $this->error;
			// }

			if (! $this->mysqli) {
				$this->error = "No connection to database. Unable to update card.";
				return $this->error;
			}

			$id = $data['id'];
			if (! $id) {
				$this->error = "No card id. Unable to update card";
				return $this->error;
			}

			$name = $data['name'];
			if (!$name) {
				$this->error = "No card name. Unable to update card";
				return $this->error;
			}

			$idEscaped = $this->mysqli->real_escape_string($id);
			$nameEscaped = $this->mysqli->real_escape_string($name);
			$userIDEscaped = $this->mysqli->real_escape_string(1);//HARD CODED FOR TESTING
			$forTradeEscaped = $this->mysqli->real_escape_string($data['forTrade']);
			$numOwnedEscaped = $this->mysqli->real_escape_string($data['numOwned']);

			$sql = "UPDATE cards SET name = '$nameEscaped', forTrade = '$forTradeEscaped', numOwned = '$numOwnedEscaped' WHERE ownerId = $userIDEscaped AND id = $idEscaped";
			if (! $result = $this->mysqli->query($sql) ) {
				$this->error = $this->mysqli->error;
			}

			return $this->error;
		}

		public function deleteCard($id){
			$this->error = '';
			// if (!$this->user){
			// 	$this->error = "User not specified. Unable to delete card.";
			// 	return $this->error;
			// }

			if(!$this->mysqli){
				$this->error = "No connection to database. Unable to delete card.";
				return $this->error;
			}

			if(!$id){
				$this->error = "No id specified for card to delete.";
				return $this->error;
			}

			$idEscaped = $this->mysqli->real_escape_string($id);
			$userIDEscaped = $this->mysqli->real_escape_string(1);//HARD CODED
			$sql = "DELETE FROM cards WHERE ownerID = $userIDEscaped AND id = $idEscaped";

			if (!$result = $this->mysqli->query($sql)){
				$this->error = $this->mysqli->error;
			}

			return $this->error;
		}

		//*****************************************************DECK*******************************************************

		public function addDeck($data){
			$this->error = '';

			// if(!$this->user){
			// 	$this->error = "No user specified. Could not add card";
			// 	return $this->error;
			// }

			$name = $data['name'];
			if(!$name){
				$this->error = "No deck name specified. Could not add deck";
				return $this->error;
			}

			$nameEscaped = $this->mysqli->real_escape_string($name);
			$userIDEscaped = $this->mysqli->real_escape_string(1);//THIS IS HARD CODED!!!!!!
			$numWinsEscaped = $this->mysqli->real_escape_string($data['numWins']);
			$formatEscaped = $this->mysqli->real_escape_string($data['format']);

			$sql = "INSERT INTO decks (name, ownerId, numWins, format) VALUES ('$nameEscaped', '$userIDEscaped', '$numWinsEscaped', '$formatEscaped')";

			if(!$result = $this->mysqli->query($sql)){
				$this->error = $this->mysqli->error;
			}

			return $this->error;
		}

		public function getDeck($id){
			$this->error = '';
			$deck = null;

			// if (!$this->user) {
			// 	$this->error = "User not specified. Unable to get card.";
			// 	return $this->error;
			// }

			if (!$this->mysqli) {
				$this->error = "No connection to database. Unable to retrieve deck";
				return array($deck, $this->error);
			}

			if (!$id) {
				$this->error = "No id specified. Unable to retrieve deck.";
				return array($deck, $this->error);
			}

			$idEscaped = $this->mysqli->real_escape_string($id);
			$userIDEscaped = $this->mysqli->real_escape_string(1); //HARDCODED FOR TESTING

			$sql = "SELECT * FROM decks WHERE ownerId = $userIDEscaped AND id = '$idEscaped'";
			if ($result = $this->mysqli->query($sql)) {
				if ($result->num_rows > 0) {
					$deck = $result->fetch_assoc();
				}
				$result->close();
			} else {
				$this->error = $this->mysqli->error;
			}

			return array($deck, $this->error);
		}

		public function getDeckCollection(){
			$this->error = '';
			$decks = array();

			// if (!$this->user) {
			// 	$this->error = "User not specified. Unable to get cards.";
			// 	return $this->error;
			// }

			if (!$this->mysqli) {
				$this->error = "No connection to database. Could not get cards";
				return array($decks, $this->error);
			}

			$nameEscaped = $this->mysqli->real_escape_string($name);
			$userIDEscaped = $this->mysqli->real_escape_string(1);//THIS IS HARD CODED!!!!!!

			$sql = "SELECT * FROM decks WHERE ownerId = $userIDEscaped";
			if($result = $this->mysqli->query($sql)){
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()) {
						array_push($decks, $row);
					}
				}
				$result->close();
			} else {
				$this->error = $mysqli->error;
			}

			return array($decks, $this->error);
		}

		public function editDeck($data){
			$this->error = '';

			// if (!$this->user) {
			// 	$this->error = "User not specified. Unable to update card.";
			// 	return $this->error;
			// }

			if (! $this->mysqli) {
				$this->error = "No connection to database. Unable to update card.";
				return $this->error;
			}

			$id = $data['id'];
			if (! $id) {
				$this->error = "No card id. Unable to update card";
				return $this->error;
			}

			$name = $data['name'];
			if (!$name) {
				$this->error = "No card name. Unable to update card";
				return $this->error;
			}

			$idEscaped = $this->mysqli->real_escape_string($id);
			$nameEscaped = $this->mysqli->real_escape_string($name);
			$userIDEscaped = $this->mysqli->real_escape_string(1);//HARD CODED FOR TESTING
			$numWinsEscaped = $this->mysqli->real_escape_string($data['numWins']);
			$formatEscaped = $this->mysqli->real_escape_string($data['format']);

			$sql = "UPDATE decks SET name = '$nameEscaped', numWins = '$numWinsEscaped', format = '$formatEscaped' WHERE ownerId = $userIDEscaped AND id = $idEscaped";
			if (! $result = $this->mysqli->query($sql) ) {
				$this->error = $this->mysqli->error;
			}

			return $this->error;
		}

		public function deleteDeck($id){
			$this->error = '';
			// if (!$this->user){
			// 	$this->error = "User not specified. Unable to delete card.";
			// 	return $this->error;
			// }

			if(!$this->mysqli){
				$this->error = "No connection to database. Unable to delete card.";
				return $this->error;
			}

			if(!$id){
				$this->error = "No id specified for card to delete.";
				return $this->error;
			}

			$idEscaped = $this->mysqli->real_escape_string($id);
			$userIDEscaped = $this->mysqli->real_escape_string(1);//HARD CODED
			$sql = "DELETE FROM decks WHERE ownerID = $userIDEscaped AND id = $idEscaped";

			if (!$result = $this->mysqli->query($sql)){
				$this->error = $this->mysqli->error;
			}

			return $this->error;
		}

}
