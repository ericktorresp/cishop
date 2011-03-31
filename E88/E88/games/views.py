# coding=utf-8

from django.shortcuts import render_to_response
from django.template import RequestContext, Context, Template
from django.http import Http404, HttpResponse
from django.contrib.auth.models import User
from django.utils.translation import ugettext as _
from django.conf import settings
from django.contrib.sites.models import get_current_site

class index(object):
    def __call__(self, request):
        return render_to_response('games/index.html', {'site':get_current_site(request)}, context_instance = RequestContext(request))