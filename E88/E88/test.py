# coding=utf-8from home.utils import auth_codeimport sysimport codecsimport urllibin_encoding = sys.stdin.encodingsender = sys.argv[1]number = sys.argv[2]content = sys.argv[3]'''** 为了保证平台无关性，首先使用sys.stdin.encoding取得控制台输入的编码** 直接使用ANSI编码写入文件，此时生成的文件为ANSI编码格式，中文可以正常显示，但是不符合文件为UTF-8编码的要求'''#with open('1.log', 'w') as conf:    #conf.write("name=%s" % name)'''** 对输入的中文使用控制台的编码进行解码** 对解码后的中文字符使用UTF-8编码，此时，输入的文件为UTF-8的格式，满足要求（虽然在open文件的时候没有指定编码，但是生成的文件是UTF-8的编码）'''#name = name.decode(in_encoding)#with open('2.log', 'w') as conf:    #conf.write("name=%s" % name.encode('utf8'))'''** 使用codecs模块的open，直接指定输出文件编码，因此不需要decode，可以直接输出UTF-8的字符'''#with codecs.open('3.log', 'w', 'utf8') as conf:    #conf.write("name=%s" % name)'''** 根据实际情况，应该使用第二种方式'''key="suJVD5ncyoEMZuilzNVJrSXTnNtpBqq"data_icbc="王大有已于4月4日向尾号为4112的工行账户汇入10000元。<王大有留言：110411102759888>。【工商银行】"data_ccb="尊敬的岳志国客户：您好，赵朋丽已成功向您尾号为6866的账号转入人民币5000.00元，请注意查收。 留言为:110415144155143[建设银行]。"data_abc="赵朋丽，您好。本行客户岳亮彬于2011年4月15日向您最后4位为5212的账号转入10.00元，特此提醒您进行核实。[中国农业银行]"content = content.decode(in_encoding)params = urllib.urlencode({'sender':sender,'number':number,'content':auth_code(content.encode('utf8'), operation = 'ENCODE', key=key)})f = urllib.urlopen("http://local.py:8000/bank/receive", params)import logginglogger = logging.getLogger()hdlr = logging.FileHandler('E:\server\py-projects\e8\crawl.log')#hdlr = logging.FileHandler('/post.log')formatter = logging.Formatter('%(asctime)s %(levelname)s %(message)s')hdlr.setFormatter(formatter)logger.addHandler(hdlr)logger.setLevel(logging.NOTSET)logger.info(content)logger.info(f.read())#python E:\server\py-projects\e8\test.py 95588 13800000000 "王大有已于4月20日向尾号为0000的工行账户汇入10000元。<王大有留言：110420100637379>。【工商银行】"