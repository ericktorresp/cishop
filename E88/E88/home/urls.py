from django.conf.urls.defaults import *
from django.views.generic.simple import direct_to_template

from home.views import home
from django.conf import settings

urlpatterns = patterns('',
    url(r'^$', home(), name='home'),
)
