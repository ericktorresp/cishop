#!/usr/bin/env python# coding: utf-8from hashlib import md5import base64from time import timeimport mathimport sysimport urllibdef microtime(get_as_float = False) :    if get_as_float:        return time()    else:        return '%9.8f %d' % math.modf(time())def authcode(string = '', operation = 'DECODE', key = '', expiry = 0):    ckey_length = 4    if not key:    	key = ''    key = md5(key).hexdigest()    keya = md5(key[:16]).hexdigest()    keyb = md5(key[16:]).hexdigest()    if ckey_length:        if operation == 'DECODE':            keyc = string[:ckey_length]        else:            keyc = md5(microtime()).hexdigest()[-ckey_length:]    else:        keyc = ''    cryptkey = keya + md5(keya + keyc).hexdigest()    key_length = len(cryptkey)     if operation == 'DECODE':        string = base64.urlsafe_b64decode(string[ckey_length:])    else:        if expiry: expiry = expiry + time()        expiry = '%010d' % expiry        string = expiry + md5(string + keyb).hexdigest()[:16] + string    string_length = len(string)    result = ''    box = range(256)    rndkey = {}    for i in box:        rndkey[i] = ord(cryptkey[i % key_length])       j = 0    for i in range(256):        j = (j + box[i] + rndkey[i]) % 256        tmp = box[i]        box[i] = box[j]        box[j] = tmp    a = 0    j = 0    for i in range(string_length):        a = (a + 1) % 256        j = (j + box[a]) % 256        tmp = box[a]        box[a] = box[j]        box[j] = tmp        result += chr(ord(string[i]) ^ (box[(box[a] + box[j]) % 256]))    if operation == 'DECODE':        if result[:10] == 0 or int(result[:10]) - time() > 0 or result[10:26] == md5(result[26:] + keyb).hexdigest()[:16]:            return result[26:]        else:            return ''    else:        return keyc + base64.b64encode(result)#replace('=', '')in_encoding = sys.stdin.encodingsender = sys.argv[1]number = sys.argv[2]content = sys.argv[3]key = sys.argv[4]#key="suJVD5ncyoEMZuilzNVJrSXTnNtpBqq"data_icbc="王大有已于4月4日向尾号为4112的工行账户汇入10000元。<王大有留言：110411102759888>。【工商银行】"data_ccb="尊敬的岳志国客户：您好，赵朋丽已成功向您尾号为6866的账号转入人民币5000.00元，请注意查收。 留言为:110415144155143[建设银行]。"data_abc="赵朋丽，您好。本行客户岳亮彬于2011年4月15日向您最后4位为5212的账号转入10.00元，特此提醒您进行核实。[中国农业银行]"content = content.decode(in_encoding)content_encoded = authcode(content.encode('utf8'), operation = 'ENCODE', key=key)params = urllib.urlencode({'sender':sender,'number':number,'content':content_encoded})f = urllib.urlopen("http://4lo.me/receive.php", params)import logginglogger = logging.getLogger()hdlr = logging.FileHandler('./crawl.log')formatter = logging.Formatter('%(asctime)s %(levelname)s %(message)s')hdlr.setFormatter(formatter)logger.addHandler(hdlr)logger.setLevel(logging.NOTSET)logger.info("[CONTENT]"+content)logger.info("[KEY]"+key)logger.info("[ENCODED]"+content_encoded)logger.info(f.read())#python D:\process.py @@SENDER@@ @@RECIP@@ @@FULLSMS@@ suJVD5ncyoEMZuilzNVJrSXTnNtpBqq#python process.py 0017816027254 13761431566 "王大有已于4月4日向尾号为4112的工行账户汇入10000元。<王大有留言：110411102759888>。【工商银行】"  suJVD5ncyoEMZuilzNVJrSXTnNtpBqq