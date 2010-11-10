/**
 * 
 */
package com.ph918.extensions;

import java.nio.channels.SocketChannel;
import java.util.*;

import org.json.JSONObject;

import it.gotoandplay.smartfoxserver.crypto.MD5;
import it.gotoandplay.smartfoxserver.data.*;
import it.gotoandplay.smartfoxserver.db.*;
import it.gotoandplay.smartfoxserver.exceptions.*;
import it.gotoandplay.smartfoxserver.extensions.*;
import it.gotoandplay.smartfoxserver.lib.ActionscriptObject;
import it.gotoandplay.smartfoxserver.events.InternalEventObject;

/**
 * @author Floyd
 * 
 */
public class Login extends AbstractExtension {
	private ExtensionHelper helper;
	private Zone currentZone;
	private DbManager db;

	/**
	 * Initializion point:
	 * 
	 * this method is called as soon as the extension is loaded in the server.
	 * 
	 * You can add here all the initialization code
	 */
	public void init() {
		trace("Login Extension initialized");
		helper = ExtensionHelper.instance();
		this.currentZone = helper.getZone(this.getOwnerZone());
	}

	/**
	 * This method is called by the server when an extension is being removed /
	 * destroyed.
	 * 
	 * Always make sure to release resources like setInterval(s) open files etc
	 * in this method.
	 * 
	 * In this case we delete the reference to the databaseManager
	 */
	public void destroy() {
		trace("Login Extension Destroyed");
	}

	/**
	 * Handle Client Requests in XML format
	 * 
	 * @param cmd
	 *            the command name
	 * @param ao
	 *            the actionscript object with the request params
	 * @param u
	 *            the user who sent the request
	 * @param fromRoom
	 *            the roomId where the request was generated
	 */
	public void handleRequest(String cmd, ActionscriptObject ao, User u,
			int fromRoom) {
		// Your code here
	}

	/**
	 * Handle Client Requests in String format
	 * 
	 * @param cmd
	 *            the command name
	 * @param params
	 *            an array of String parameters
	 * @param u
	 *            the user who sent the request
	 * @param fromRoom
	 *            the roomId where the request was generated
	 */
	public void handleRequest(String cmd, String params[], User u, int fromRoom) {
		// Your code here
	}

	/**
	 * Handle Client Requests in JSON format
	 * 
	 * @param cmd
	 *            the command name
	 * @param params
	 *            a JSONObject with the request parameters
	 * @param u
	 *            the user who sent the request
	 * @param fromRoom
	 *            the roomId where the request was generated
	 */
	public void handleRequest(String cmd, JSONObject jso, User u, int fromRoom) {
		// Your code here
	}

	/**
	 * Handle Internal Server Events
	 * 
	 * @param ieo the event object
	 */
	public void handleInternalEvent(InternalEventObject ieo)
	{
		if (ieo.getEventName().equals("loginRequest"))
		{
			// 根据配置文件里的DatabaseManager配置获得当前Zone的数据库操作对象
			db = this.currentZone.dbManager;
			ActionscriptObject response = new ActionscriptObject();
			User loginUser = null;
 
			// 获取用户名
			String nick = ieo.getParam("nick");
			// 获取客户端利用服务器加密字符串和用户密码加密后MD5密码
			String ClientPassword = ieo.getParam("pass");
			// 获取服务器socket通道
			SocketChannel chan = (SocketChannel)ieo.getObject("chan");
			// 根据socket通道生成加密字符
			String ServerRandom = helper.getSecretKey(chan);
			// 获取数据库中用户的密码
			ArrayList<DataRow> arrList = db.executeQuery("SELECT password FROM users WHERE username='"+nick+"'");
			String ServerPassword = "";
			boolean IsLogin = false;
			if (arrList.size() <= 0)
			{
				response.put("_cmd", "loginKO");
				response.put("err", "用户名不存在，登录失败。");
			}
			else
			{
				// 获取数据库密码
				DataRow dr = arrList.get(0);
				String dbpass = dr.getItem("password");
				// 根据服务器加密字符和数据库用户密码，生成服务器端的混合MD5密码
				ServerPassword = MD5.instance().getHash(ServerRandom + dbpass);
				IsLogin = (ClientPassword.equals(ServerPassword)) ? true : false;
				if (IsLogin)
				{
					try
					{
						// 登录成功
						loginUser = helper.canLogin(nick, ClientPassword, chan, this.currentZone.getName());
						response.put("_cmd", "loginOK");
						response.put("id", String.valueOf(loginUser.getUserId()));
						response.put("name", loginUser.getName());
					}
					catch (LoginException e)
					{
						// 登录失败
						response.put("_cmd", "loginKO");
						response.put("err", e.getMessage());
					}
				}
				else
				{
					response.put("_cmd", "loginKO");
					response.put("err", "认证失败.");
				}
			}
			LinkedList<SocketChannel> linkedlist = new LinkedList<SocketChannel>();
			linkedlist.add(chan);
			// 返回结果给客户端
			this.sendResponse(response, -1, null, linkedlist);
			if (IsLogin)
				helper.sendRoomList(chan);
		}
	}
}
