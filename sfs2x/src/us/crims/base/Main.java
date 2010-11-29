package us.crims.base;

import com.smartfoxserver.v2.annotations.Instantiation;
import com.smartfoxserver.v2.annotations.Instantiation.InstantiationMode;
import com.smartfoxserver.v2.core.SFSEventType;
import com.smartfoxserver.v2.db.IDBManager;
import com.smartfoxserver.v2.extensions.SFSExtension;

@Instantiation(InstantiationMode.SINGLE_INSTANCE)
public class Main extends SFSExtension
{
	public static final String ZONE = "";
	@SuppressWarnings("unused")
	private IDBManager dbManager;
	@Override
	public void init()
	{
		trace("Hello, this is my first SFS2X Extension!");
		dbManager = getParentZone().getDBManager();
		addEventHandler(SFSEventType.USER_LOGIN, LoginEventHandler.class);
		addRequestHandler("move", MoveReqHandler.class);
//		addEventHandler(SFSEventType.USER_JOIN_ZONE, JoinZoneEventHandler.class);
	}
	
	@Override
	public void destroy()
	{
	    super.destroy();
	    /*
	    * More code here...
	    */
	}
}
