package us.crims.base;

import com.smartfoxserver.bitswarm.sessions.ISession;
import com.smartfoxserver.v2.SmartFoxServer;
import com.smartfoxserver.v2.api.ISFSApi;
import com.smartfoxserver.v2.core.ISFSEvent;
import com.smartfoxserver.v2.core.SFSEventParam;
import com.smartfoxserver.v2.db.IDBManager;
import com.smartfoxserver.v2.entities.data.ISFSArray;
import com.smartfoxserver.v2.entities.data.ISFSObject;
import com.smartfoxserver.v2.entities.data.SFSObject;

import com.smartfoxserver.v2.exceptions.SFSErrorCode;
import com.smartfoxserver.v2.exceptions.SFSErrorData;
import com.smartfoxserver.v2.exceptions.SFSException;
import com.smartfoxserver.v2.exceptions.SFSLoginException;
import com.smartfoxserver.v2.exceptions.SFSJoinRoomException;

import com.smartfoxserver.v2.extensions.BaseServerEventHandler;

import com.smartfoxserver.v2.api.SFSApi;

import com.smartfoxserver.v2.entities.Room;
import com.smartfoxserver.v2.entities.User;
import com.smartfoxserver.v2.entities.Zone;

import java.sql.SQLException;


@SuppressWarnings("unused")
public class LoginEventHandler extends BaseServerEventHandler {
	// Obtain the DBManager and reference the zone name specified in Main.java
	private IDBManager _dbManager = SmartFoxServer.getInstance().getZoneManager().getZoneByName(Main.ZONE).getDBManager();
	// Bad username/password error
	private SFSErrorData _errData = new SFSErrorData(SFSErrorCode.LOGIN_BAD_USERNAME);
	
	@Override
	public void handleServerEvent(ISFSEvent event) throws SFSException {
		trace("<-LOGIN EVENT->");
		ISession session = (ISession) event.getParameter(SFSEventParam.SESSION);
		String username = (String) event.getParameter(SFSEventParam.LOGIN_NAME);
		String encryptedPass = (String) event.getParameter(SFSEventParam.LOGIN_PASSWORD);
		// ?
		//ISFSApi smartfox = SmartFoxServer.getInstance().getAPIManager().getSFSApi();
		ISFSArray userResult = null;
		
		String userSQL = "SELECT * FROM users WHERE username = '" + username + "' LIMIT 1";
		
		try {
			userResult = (ISFSArray) _dbManager.executeQuery(userSQL);
			trace("<-RESULT->" + userResult);
		} catch(SQLException sqlErr) {
			trace("<-SQL Exception->" + sqlErr);
			throw new SFSLoginException("Username or password incorrect.Error1", _errData);
		}
		
		// At this point I would have added a conditional check to see if there was a result,
		// but it is no longer needed because if no fields were retrieved, then it would throw an SFSLoginException and
		// would not get this far.
		
		if(userResult.size() == 1) {
			ISFSObject userData = userResult.getSFSObject(0);
			String dbPass = userData.getUtfString("password");
			trace("<-SESSION->" + session);
			trace("<-USERNAME->" + username);
			trace("<-DB PASSWORD->" + dbPass);
			trace("<-ENCRYPTED PASS->" + encryptedPass);
			if(getApi().checkSecurePassword(session, dbPass, encryptedPass)) {
				trace("<-both passwords correct->");
				// Remove unwanted fields from the data (these don't need to be sent to the client)
				//userData = cleanUserData((SFSObject) userData);
				ISFSObject userInfo = new SFSObject();
				/*userInfo.putUtfString("lv", userData.getUtfString("LV"));
				userInfo.putUtfString("exp", userData.getUtfString("EXP"));
				userInfo.putUtfString("country", userData.getUtfString("COUNTRY"));
				userInfo.putUtfString("mw", userData.getUtfString("MW"));
				userInfo.putUtfString("sw", userData.getUtfString("SW"));*/
				
				// Store userData in the current session. This is used later on in ZoneEventHandler to add custom user variables
				//session.setProperty("userInfo", userInfo);
				
				// Obtain the response object in order to send custom userData to the client upon login.
				ISFSObject responseObj = (ISFSObject) event.getParameter(SFSEventParam.LOGIN_OUT_DATA);
				
				// Add userData to the response object
				responseObj.putSFSObject("userInfo", userInfo);
			} else {
				throw new SFSLoginException("username or password incorrect.Error2", _errData);
			}
		} else {
			throw new SFSLoginException("username or password incorrect.Error3", _errData);
		}
	}
	
	private SFSObject cleanUserData(SFSObject sfsObj) {
		trace("cleaning");
		sfsObj.removeElement("password");
		sfsObj.removeElement("email");
		sfsObj.removeElement("firstName");
		sfsObj.removeElement("lastName");
		sfsObj.removeElement("subscribe");
		return (SFSObject) sfsObj;
	}
}
