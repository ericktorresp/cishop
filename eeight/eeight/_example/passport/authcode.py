#!/usr/bin/env python
# coding: utf-8
from hashlib import md5
import base64
from time import time
import math

def microtime(get_as_float = False) :
    if get_as_float:
        return time()
    else:
        return '%9.8f %d' % math.modf(time())

_auth_key = 'suJVD5ncyoEMZuilzNVJrSXTnNtpBqq'

def authcode(string = '', operation = 'DECODE', key = '', expiry = 0):
    ckey_length = 4
    if not key:
    	key = _auth_key
    key = md5(key).hexdigest()
    keya = md5(key[:16]).hexdigest()
    keyb = md5(key[16:]).hexdigest()
    if ckey_length:
        if operation == 'DECODE':
            keyc = string[:ckey_length]
        else:
            keyc = md5(microtime()).hexdigest()[-ckey_length:]
    else:
        keyc = ''
    cryptkey = keya + md5(keya + keyc).hexdigest()
    key_length = len(cryptkey) 
    if operation == 'DECODE':
        string = base64.urlsafe_b64decode(string[ckey_length:])
    else:
        if expiry: expiry = expiry + time()
        expiry = '%010d' % expiry
        string = expiry + md5(string + keyb).hexdigest()[:16] + string
    string_length = len(string)

    result = ''
    box = range(256)
    rndkey = {}
    for i in box:
        rndkey[i] = ord(cryptkey[i % key_length])
   
    j = 0
    for i in range(256):
        j = (j + box[i] + rndkey[i]) % 256
        tmp = box[i]
        box[i] = box[j]
        box[j] = tmp
    a = 0
    j = 0
    for i in range(string_length):
        a = (a + 1) % 256
        j = (j + box[a]) % 256
        tmp = box[a]
        box[a] = box[j]
        box[j] = tmp
        result += chr(ord(string[i]) ^ (box[(box[a] + box[j]) % 256]))
    if operation == 'DECODE':
        if result[:10] == 0 or int(result[:10]) - time() > 0 or result[10:26] == md5(result[26:] + keyb).hexdigest()[:16]:
            return result[26:]
        else:
            return ''
    else:
        return keyc + base64.b64encode(result)#replace('=', '')

str1 = '王大有已于4月4日向尾号为4112的工行账户汇入10000元。<王大有留言：110411102759888>。【工商银行】'
encoded = '7177XJSkvRJvmbC-Ei91EJWM_d849ATp9xXummNTraL7FZ7Rp_ppZDdz9tx8lgdcQ6aa2omi_hkBUriGOsAi2X8P_J9FYdn1j0UdNWuTjeCZlTvOXcOw8Y-hPhZ8DZAWQjnD3ddotns2U9V5lxY21bA6w06YRkEWL_nXlKG3DAxwPNRtBeMg800fpehejZtYKIZc3ZSBSA6eCoeG'
encode_str = authcode(str1, 'ENCODE') #加密
print encode_str
print authcode(encode_str) #解密