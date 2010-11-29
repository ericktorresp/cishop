package us.crims.base;

import com.smartfoxserver.v2.annotations.Instantiation;
import com.smartfoxserver.v2.annotations.Instantiation.InstantiationMode;
import com.smartfoxserver.v2.entities.Room;
import com.smartfoxserver.v2.entities.User;
import com.smartfoxserver.v2.entities.data.ISFSObject;
import com.smartfoxserver.v2.extensions.BaseClientRequestHandler;

@Instantiation(InstantiationMode.SINGLE_INSTANCE)
public class MoveReqHandler extends BaseClientRequestHandler {
	@Override
	public void handleClientRequest(User sender, ISFSObject params) {
		trace(sender.getName(), " request!! ");
		Room room = sender.getLastJoinedRoom();

		params.putUtfString("name", sender.getName());
		send("p", params, room.getUserList());
	}
}
