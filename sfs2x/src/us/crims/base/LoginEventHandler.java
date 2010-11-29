package us.crims.base;

import com.smartfoxserver.v2.core.ISFSEvent;
import com.smartfoxserver.v2.core.SFSEventParam;
import com.smartfoxserver.v2.entities.data.ISFSObject;
import com.smartfoxserver.v2.exceptions.SFSException;
import com.smartfoxserver.v2.exceptions.SFSLoginException;
import com.smartfoxserver.v2.extensions.BaseServerEventHandler;
import com.smartfoxserver.bitswarm.sessions.ISession;
import com.smartfoxserver.v2.db.*;

public class LoginEventHandler extends BaseServerEventHandler {
	private SFSDBManager db;
	@Override
	public void handleServerEvent(ISFSEvent event) throws SFSException {
		String name = (String) event.getParameter(SFSEventParam.LOGIN_NAME);
		String pass = (String) event.getParameter(SFSEventParam.LOGIN_PASSWORD);
		ISession session = (ISession) event.getParameter(SFSEventParam.SESSION);
		ISFSObject paramsOut = (ISFSObject) event
				.getParameter(SFSEventParam.LOGIN_OUT_DATA);
		trace(name + " is login .. ");
		if (name.startsWith("h_")
				&& getApi().checkSecurePassword(session, "123", pass)) {
			paramsOut.putBool("login", true);
		} else {
			paramsOut.putBool("login", false);
			// throw new SFSLoginException("用户名必须以\"h_\"开头且密码为:123");
		}
	}
}
