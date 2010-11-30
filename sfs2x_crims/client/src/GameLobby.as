/**
 * SmartFoxServer 2X Examples - GameLobby
 * http://www.smartfoxserver.com
 * (c) 2010 gotoAndPlay
 */
import com.smartfoxserver.smartfoxbits.bits.UserList;
import com.smartfoxserver.v2.SmartFox;
import com.smartfoxserver.v2.core.SFSEvent;
import com.smartfoxserver.v2.entities.Room;
import com.smartfoxserver.v2.entities.User;
import com.smartfoxserver.v2.entities.data.ISFSObject;
import com.smartfoxserver.v2.entities.data.SFSObject;
import com.smartfoxserver.v2.entities.invitation.Invitation;
import com.smartfoxserver.v2.entities.invitation.InvitationReply;
import com.smartfoxserver.v2.entities.match.MatchExpression;
import com.smartfoxserver.v2.entities.match.NumberMatch;
import com.smartfoxserver.v2.entities.match.StringMatch;
import com.smartfoxserver.v2.entities.variables.ReservedRoomVariables;
import com.smartfoxserver.v2.entities.variables.SFSUserVariable;
import com.smartfoxserver.v2.entities.variables.UserVariable;
import com.smartfoxserver.v2.requests.JoinRoomRequest;
import com.smartfoxserver.v2.requests.SetUserVariablesRequest;
import com.smartfoxserver.v2.requests.game.CreateSFSGameRequest;
import com.smartfoxserver.v2.requests.game.InvitationReplyRequest;
import com.smartfoxserver.v2.requests.game.SFSGameSettings;
import com.smartfoxserver.v2.util.ClientDisconnectionReason;

import components.CreateGamePanel;
import components.InvitationPanel;

import flash.utils.getTimer;

import mx.collections.ArrayCollection;
import mx.collections.Sort;
import mx.collections.SortField;
import mx.controls.Alert;
import mx.controls.List;
import mx.events.CloseEvent;
import mx.managers.PopUpManager;

private const THE_LOBBY_NAME:String = "The Lobby";
private const USERVAR_COUNTRY:String = "country";
private const USERVAR_RANKING:String = "rank";

private var sfs:SmartFox;
private var alert:Alert;
private var createGamePanel:CreateGamePanel;
private var invitationPanel:InvitationPanel;
private var invitationsQueue:Array;

private function init():void
{
	// Get reference to SmartFoxServer connection
	sfs = loginPanel.connector.connection;
	sfs.addEventListener(SFSEvent.LOGIN, onLogin);
	sfs.addEventListener(SFSEvent.CONNECTION_LOST, onConnectionLost);
	sfs.addEventListener(SFSEvent.USER_VARIABLES_UPDATE, onUserVariablesUpdate);
	sfs.addEventListener(SFSEvent.ROOM_JOIN, onRoomJoin);
	sfs.addEventListener(SFSEvent.USER_ENTER_ROOM, onUserEnterRoom);
	sfs.addEventListener(SFSEvent.USER_EXIT_ROOM, onUserExitRoom);
	sfs.addEventListener(SFSEvent.INVITATION, onInvitation);
	sfs.addEventListener(SFSEvent.ROOM_CREATION_ERROR, onRoomCreationError);
	
	// Create game creation panel instance
	createGamePanel = new CreateGamePanel();
	createGamePanel.addEventListener(CloseEvent.CLOSE, onPopUpClosed);
	createGamePanel.initialize();
	
	// Initialize invitations queue
	invitationsQueue = new Array();
}

//---------------------------------
// User interaction event handlers
//---------------------------------

/**
 * Countries dropdown change event listener.
 * Update user variables saving player details.
 */
public function onPlayerDetailsChange():void
{
	var countryUV:UserVariable = new SFSUserVariable(USERVAR_COUNTRY, dd_country.selectedLabel);
	var rankUV:UserVariable = new SFSUserVariable(USERVAR_RANKING, ns_ranking.value);
	
	var request:SetUserVariablesRequest = new SetUserVariablesRequest([countryUV,rankUV]);
	sfs.send(request);
}

/**
 * Create game button click event listener.
 * Show a popup panel where user can set the game properties.
 */
private function onCreateGameApplicationBtClick():void
{
	createGamePanel.reset();
	PopUpManager.addPopUp(createGamePanel, this, true)
	PopUpManager.centerPopUp(createGamePanel);
}

/**
 * Create game button click event listener (create game panel).
 * Create a new game using the parameters entered in the create game pupup. 
 */
public function onCreateGamePopuUpBtClick():void
{
	if (createGamePanel.ti_name.length > 0)
	{
		// Create game settings
		var settings:SFSGameSettings = new SFSGameSettings(createGamePanel.ti_name.text);
		settings.groupId = "games";
		settings.maxUsers = createGamePanel.ns_maxPlayers.value;
		settings.minPlayersToStartGame = createGamePanel.ns_minPlayers.value;
		settings.isPublic = createGamePanel.rb_isPublic.selected;
		settings.leaveLastJoinedRoom = true;
		settings.notifyGameStarted = true;
		
		if (!settings.isPublic) // This check is superfluous: if the game is public the invitation-related settings are ignored
		{
			settings.invitedPlayers = createGamePanel.ls_players.selectedItems;
			settings.searchableRooms = ["default"]; // Search the "default" group, which in this example contains The Lobby room only
			
			// Create invitation additional params
			var invParams:ISFSObject = new SFSObject();
			invParams.putUtfString("gameType", createGamePanel.dd_type.selectedLabel);
			invParams.putUtfString("room", createGamePanel.ti_name.text);
			invParams.putUtfString("message", createGamePanel.ta_invitationMsg.text);
			settings.invitationParams = invParams;
		}
		
		// Create match expression
		var matchExp:MatchExpression = new MatchExpression(USERVAR_COUNTRY, StringMatch.EQUALS, createGamePanel.lb_country.text);
		matchExp.and(USERVAR_RANKING, NumberMatch.GREATER_THAN_OR_EQUAL_TO, Number(createGamePanel.lb_minRanking.text));
		settings.playerMatchExpression = matchExp;
		
		// Create room
		var request:CreateSFSGameRequest = new CreateSFSGameRequest(settings);
		sfs.send(request);
		
		// Close popup
		removePopUp();
	}
}

/**
 * Accept/refuse buttons click event listener (invitation panel).
 * Accept or refuse the invitation to join a game. 
 */
public function onInvitationPanelResponse(accept:Boolean, invitation:Invitation):void
{
	// Close popup
	removePopUp();
	
	// Accept/refuse invitation
	var request:InvitationReplyRequest = new InvitationReplyRequest(invitation, (accept ? InvitationReply.ACCEPT : InvitationReply.REFUSE));
	sfs.send(request);
	
	// If invitation was accepted, refuse all remaining invitations in the queue
	if (accept)
	{
		// Refuse othe invitations
		for each (var otherInv:Object in invitationsQueue)
			sfs.send(new InvitationReplyRequest(otherInv.invitation, InvitationReply.REFUSE));
		
		invitationsQueue = [];
	}
	
	// If invitation was refused, process next invitation in the queue (if any)
	else
	{
		while (invitationsQueue.length > 0)
		{
			var obj:Object = invitationsQueue.splice(0, 1)[0];
			var invitation:Invitation = obj.invitation;
			
			// Evaluate remaining time for replying
			var elapsed:int = Math.ceil((getTimer() - obj.time) / 1000);
			var remaining:int = invitation.secondsForAnswer - elapsed;
			
			// Display invitation only if expiration will occur in 3 seconds or more
			if (remaining >= 3)
			{
				processInvitation(invitation, remaining);
				break;
			}
		}
	}
}

/**
 * Leave game button click event listener.
 * Join the lobby room. 
 */
private function onLeaveGameBtClick():void
{
	var request:JoinRoomRequest = new JoinRoomRequest(THE_LOBBY_NAME);
	sfs.send(request);
}

private function onAlertClosed(evt:Event):void
{
	removeAlert();
}

private function onPopUpClosed(evt:Event):void
{
	removePopUp();
}

//---------------------------------
// SmartFoxServer event handlers
//---------------------------------

/**
 * On login, show the lobby view.
 */
private function onLogin(evt:SFSEvent):void
{
	// Move to chat view, and display user name 
	mainView.selectedChild = view_lobby;
	lb_myUserName.text = sfs.mySelf.name;
	
	// Set initial player details
	onPlayerDetailsChange();
}

/**
 * On connection lost, go back to login panel view and display disconnection error message.
 */
private function onConnectionLost(evt:SFSEvent):void
{
	// Remove create game popup, if any
	removePopUp();
	
	// Remove alert, if displayed
	removeAlert();
	
	// Show disconnection message, unless user chose voluntarily to close the connection
	if (evt.params.reason != ClientDisconnectionReason.MANUAL)
	{
		var msg:String = "Connection lost";
		
		switch (evt.params.reason)
		{
			case ClientDisconnectionReason.IDLE:
				msg += "\nYou have exceeded the maximum user idle time";
				break;
			
			case ClientDisconnectionReason.KICK:
				msg += "\nYou have been kicked";
				break;
			
			case ClientDisconnectionReason.BAN:
				msg += "\nYou have been banned";
				break;
			
			case ClientDisconnectionReason.UNKNOWN:
				msg += " due to unknown reason\nPlease check the server-side log";
				break;
		}
		
		loginPanel.ta_error.text = msg;
	}
	
	// Show login view
	mainView.selectedChild = view_connecting;
}

/**
 * On user variables update, refresh the players list and update game creation panel.
 */
private function onUserVariablesUpdate(evt:SFSEvent):void
{
	ul_players.refreshList();
	
	// Update country and ranking in game creation panel
	var user:User = evt.params.user as User;
	if (user.isItMe)
	{
		createGamePanel.lb_country.text = user.getVariable(USERVAR_COUNTRY).getStringValue();
		createGamePanel.lb_minRanking.text = user.getVariable(USERVAR_RANKING).getIntValue().toString();
	}
}

/**
* On room joined successfully, populate the users list in create room panel, or go to the game view.
*/
private function onRoomJoin(evt:SFSEvent):void
{
	var room:Room = evt.params.room;
	
	if (room.name == THE_LOBBY_NAME)
	{
		// Show lobby view
		mainView.selectedChild = view_lobby;
		
		// Retrive the users list
		var userList:Array = room.userList;
		
		// Create the users list interface component data provider
		var dataProvider:ArrayCollection = new ArrayCollection(userList);
		
		// Remove current user
		dataProvider.removeItemAt(dataProvider.getItemIndex(sfs.mySelf));
		
		// Apply sorting
		var sort:Sort = new Sort();
		sort.fields = [new SortField("name")];
		dataProvider.sort = sort;
		
		// Assign data provider to users list component
		createGamePanel.ls_players.dataProvider = dataProvider;
		dataProvider.refresh();
	}
	else
	{
		// Go to game view
		mainView.selectedChild = view_game;
	}
}

/**
 * On user entering the current room, show his/her name in the users list of the game creation panel.
 */
private function onUserEnterRoom(evt:SFSEvent):void
{
	var room:Room = evt.params.room;
	
	if (room.name == THE_LOBBY_NAME)
	{
		var user:User = evt.params.user;
		
		// Add user to list
		var dataProvider:ArrayCollection = createGamePanel.ls_players.dataProvider as ArrayCollection;
		dataProvider.addItem(user);
	}
}

/**
 * On user leaving the current room, remove his/her name from the users list of the game creation panel.
 */
private function onUserExitRoom(evt:SFSEvent):void
{
	var room:Room = evt.params.room;
	
	if (room.name == THE_LOBBY_NAME)
	{
		var user:User = evt.params.user;
		
		// Remove user from list
		var dataProvider:ArrayCollection = createGamePanel.ls_players.dataProvider as ArrayCollection;
		var index:int = dataProvider.getItemIndex(user);
		
		if (index > -1)
		{
			dataProvider.removeItemAt(index);
			dataProvider.refresh();
		}
	}
}

/**
 * On invitation, display the invitation accept/refuse panel.
 */
private function onInvitation(evt:SFSEvent):void
{
	// Retrieve invitation data
	var invitation:Invitation = evt.params.invitation;
	
	// Display invitation panel
	processInvitation(invitation, invitation.secondsForAnswer);
}

/**
 * On game creation error, show an alert.
 */
private function onRoomCreationError(evt:SFSEvent):void
{
	// Show alert
	showAlert("Unable to create game due to the following error: " + evt.params.errorMessage);
}

//---------------------------------
// Other methods
//---------------------------------

/**
 * Generate label for each user in the players list.
 */
private function getUserLabel(item:Object):String
{
	var user:User = item.userData;
	var label:String = "<font";
	
	if (item.newMsgCount > 0)
		label += " color='#" + ul_players.newPrivateMsgLabelColor.toString(16) + "'";
	
	label += ">" + user.name + "</font>";
	
	if (user.getVariable(USERVAR_COUNTRY) != null && user.getVariable(USERVAR_RANKING) != null)
		label += " (" + user.getVariable(USERVAR_COUNTRY).getStringValue() + "/" + user.getVariable(USERVAR_RANKING).getIntValue() + ")";
	
	if (item.newMsgCount > 0)
	{
		label += "<br>";
		label += "<font size='10' color='#666666'>";
		label += String(item.newMsgCount) + " message" + (item.newMsgCount > 1 ? "s" : "") + " to read";
		label += "</font>";
	}
	
	return label
}

/**
 * Generate label for each room in the games list.
 */
private function getRoomLabel(item:Object):String
{
	var room:Room = item.roomData;
	var label:String = room.name;
	
	label += "<br>";
	label += "<font size='10' color='#666666'>Players: " + room.userCount + "/" + room.maxUsers;
	label += "<br>";
	label += room.getVariable(ReservedRoomVariables.RV_GAME_STARTED).getBoolValue() ? "Match started" : "Waiting players";
	label += "</font>";
	
	return label;
	
	// NOTE: checking the reserved room variable RV_GAME_STARTED allows showing the game status
	// to players still in the lobby (game can start if the set minimum number of players is reached),
	// but it could be useful to actually start the game in the game view
}

/**
 * Process an invitation, displaying the invitation accept/refuse panel.
 */
private function processInvitation(invitation:Invitation, secsToExpiration:int):void
{
	// Remove game creation panel (if open)
	PopUpManager.removePopUp(createGamePanel);
	
	// Check if a previous invitation was received (the panel is already displayed)
	// If yes, put the new invitation in a queue
	if (invitationPanel == null)
	{
		invitationPanel = PopUpManager.createPopUp(this, InvitationPanel, true) as InvitationPanel;
		PopUpManager.centerPopUp(invitationPanel);
		invitationPanel.addEventListener(CloseEvent.CLOSE, onPopUpClosed, false, 0, true);
		
		// Get invitation custom parameters
		var invCustomParams:ISFSObject = invitation.params;
		
		var message:String = "";
		
		if (invCustomParams.getUtfString("message") != "")
			message += '"' + invCustomParams.getUtfString("message") + "'\n";
		
		message += "You have been invited by " + invitation.inviter.name + " to play " + invCustomParams.getUtfString("gameType") + " in room " + invCustomParams.getUtfString("room");
		
		// Display message in the invitation panel
		invitationPanel.txt_invitationMsg.text = message;
		
		// Display remaining time for replying
		invitationPanel.time = secsToExpiration;
		
		// Save reference to invitation in the panel itself
		invitationPanel.invitation = invitation;
	}
	else
	{
		var obj:Object = new Object();
		obj.invitation = invitation;
		obj.time = getTimer();
		
		invitationsQueue.push(obj);
	}
}

private function showAlert(message:String):void
{
	// Remove previous alert, if any
	removeAlert()
	
	// Show alert
	alert = Alert.show(message, "Warning", Alert.OK, null, onAlertClosed);
}

private function removeAlert():void
{
	if (alert != null)
		PopUpManager.removePopUp(alert);
	
	alert = null;
}

/**
 * Common method which removes all possible panels.
 */
private function removePopUp():void
{
	// Remove game creation panel
	PopUpManager.removePopUp(createGamePanel);
	
	// Remove invitation panel
	if (invitationPanel != null)
	{
		invitationPanel.time = -1; // This stops the countdown and remove event listeners
		invitationPanel.removeEventListener(CloseEvent.CLOSE, onPopUpClosed);
		PopUpManager.removePopUp(invitationPanel);
		invitationPanel = null;
	}
}