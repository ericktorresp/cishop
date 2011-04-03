from django.conf.urls.defaults import *
from django.views.generic.simple import direct_to_template

from home.views import HomeView
from django.conf import settings

urlpatterns = patterns('',
    url(r'^$', HomeView.as_view(), name='home'),
)
