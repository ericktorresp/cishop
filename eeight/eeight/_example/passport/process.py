#!/usr/bin/env python# coding: utf-8NUMBERS = {           '18702164374':'70e1b04c4c07c40de81225c8e6a7af58',           '18702161674':'a77051b40d1039b22f7950e4eed037a2',           '18702164554':'521470cda44e6eafa2daf188ca8b2377',           '18702164594':'bfbc1116ac66cd549e7431542b18c95a'           }from hashlib import md5import base64from time import timeimport mathimport sysimport urllibimport urllib2def microtime(get_as_float = False) :    if get_as_float:        return time()    else:        return '%9.8f %d' % math.modf(time())def authcode(string = '', operation = 'DECODE', key = '', expiry = 0):    ckey_length = 4    if not key:    	key = ''    key = md5(key).hexdigest()    keya = md5(key[:16]).hexdigest()    keyb = md5(key[16:]).hexdigest()    if ckey_length:        if operation == 'DECODE':            keyc = string[:ckey_length]        else:            keyc = md5(microtime()).hexdigest()[-ckey_length:]    else:        keyc = ''    cryptkey = keya + md5(keya + keyc).hexdigest()    key_length = len(cryptkey)     if operation == 'DECODE':        string = base64.urlsafe_b64decode(string[ckey_length:])    else:        if expiry: expiry = expiry + time()        expiry = '%010d' % expiry        string = expiry + md5(string + keyb).hexdigest()[:16] + string    string_length = len(string)    result = ''    box = range(256)    rndkey = {}    for i in box:        rndkey[i] = ord(cryptkey[i % key_length])       j = 0    for i in range(256):        j = (j + box[i] + rndkey[i]) % 256        tmp = box[i]        box[i] = box[j]        box[j] = tmp    a = 0    j = 0    for i in range(string_length):        a = (a + 1) % 256        j = (j + box[a]) % 256        tmp = box[a]        box[a] = box[j]        box[j] = tmp        result += chr(ord(string[i]) ^ (box[(box[a] + box[j]) % 256]))    if operation == 'DECODE':        if result[:10] == 0 or int(result[:10]) - time() > 0 or result[10:26] == md5(result[26:] + keyb).hexdigest()[:16]:            return result[26:]        else:            return ''    else:        return keyc + base64.b64encode(result)#replace('=', '')'''1. 每个号码和 key 如何对应? key = md5(number+ip)? [temporary ignore]import socketsocket.gethostbyname(socket.gethostname())2. 如何处理访问不了或访问超时? 使用 ip:port 方式架设? hosts 文件写一个不存在或其他的域名指向 ip？'''in_encoding = sys.stdin.encodingsender = sys.argv[1]number = sys.argv[2]content = sys.argv[3]key = NUMBERS.get(number)#key="suJVD5ncyoEMZuilzNVJrSXTnNtpBqq"data_icbc="王大有已于4月4日向尾号为4112的工行账户汇入10000元。<王大有留言：110411102759888>。【工商银行】"data_ccb="尊敬的岳志国客户：您好，赵朋丽已成功向您尾号为6866的账号转入人民币5000.00元，请注意查收。 留言为:110415144155143[建设银行]。"data_abc="赵朋丽，您好。本行客户岳亮彬于2011年4月15日向您最后4位为5212的账号转入10.00元，特此提醒您进行核实。[中国农业银行]"content = content.decode(in_encoding)content_encoded = authcode(content.encode('utf8'), operation = 'ENCODE', key=key)url = "http://4lo.me/?controller=default&action=receive"params = urllib.urlencode({'sender':sender,'number':number,'content':content_encoded})try:    f = urllib2.urlopen(url, data=params, timeout=10)except urllib2.URLError, e:    print e.reasonexcept urllib2.HTTPError, ee:    print ee.codefinally:    passimport logginglogger = logging.getLogger()hdlr = logging.FileHandler('./crawl.log')formatter = logging.Formatter('%(asctime)s %(levelname)s %(message)s')hdlr.setFormatter(formatter)logger.addHandler(hdlr)logger.setLevel(logging.NOTSET)logger.info("[CONTENT]"+content)logger.info("[KEY]"+key)#logger.info("[ENCODED]"+content_encoded)logger.info("[SENDER]"+sender)logger.info("[NUMBER]"+number)logger.info(f.read())'''python D:\process.py @@SENDER@@ @@RECIP@@ "@@FULLSMS@@"1. 时间违规python process.py 95533 18702164594 "尊敬的张磊客户：您好，CCB已成功向您尾号为0317的账号转入人民币500.00元，请注意查收。 留言为:20110612100439095531[建设银行]。"3. 付款户名违规python process.py 95533 18702164594 "尊敬的张磊客户：您好，XXX已成功向您尾号为0317的账号转入人民币500.00元，请注意查收。 留言为:20110618003102372953[建设银行]。"4. 收款账户违规python process.py 95533 18702164594 "尊敬的XXX客户：您好，CCB已成功向您尾号为0317的账号转入人民币500.00元，请注意查收。 留言为:20110617220722693905[建设银行]。"5. 金额违规python process.py 95533 18702164594 "尊敬的张磊客户：您好，CCB已成功向您尾号为0317的账号转入人民币300.00元，请注意查收。 留言为:20110618003256486620[建设银行]。"'''