import datetime

from django.http import Http404, HttpResponseRedirect, HttpResponse
from django.shortcuts import render_to_response, get_object_or_404
from django.template import RequestContext
from django.contrib.auth.models import User
from django.contrib.auth.decorators import login_required
from django.utils.translation import ugettext as _
from django.utils.translation import ugettext_noop
from django.core.urlresolvers import reverse
from django.conf import settings

import urllib2
import json

from game.models import Chat

def showchats(request):
    cmd = [{'cmd': 'inlinepush',
            'params': {
               'password': settings.APE_PASSWORD,
               'raw': 'postmsg',
               'channel': 'testchannel',
               'data': {
                   'message': 'Heyyyy from Django!!'
               }
           }
    }]
    url = settings.APE_SERVER + urllib2.quote(json.dumps(cmd))
    response = urllib2.urlopen(url)
    return HttpResponse(response)
