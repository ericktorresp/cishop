# coding=utf-8

from django.shortcuts import render_to_response
from django.template import RequestContext, Context, Template
from django.http import Http404, HttpResponse
from django.contrib.auth.models import User
from django.utils.translation import ugettext as _
from django.conf import settings

try:
    import json
except:
    import simplejson as json

class home(object):
    def __call__(self, request):
        return render_to_response('home/index.html', {}, context_instance = RequestContext(request))
