# coding=utf-8
from django.http import Http404, HttpResponse
from django.utils.encoding import smart_unicode
from django.utils.http import urlquote, urlencode
from django.utils import encoding

def receive(request):
    request.encoding = 'gb2312'
    return HttpResponse(request.GET.get('content',''))
