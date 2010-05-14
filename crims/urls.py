from django.conf.urls.defaults import *
from django.views.generic.simple import direct_to_template
from django.contrib import admin
from grappelli.sites import GrappelliSite
from django.utils.translation import ugettext_lazy as _

from views import home

admin.site = GrappelliSite()
admin.autodiscover()

admin.site.groups = {
    0: {
#        'title': 'User Management Administration', # optional
        'name': _('user'),
        'apps': ['auth', 'registration'],
#        'template': 'custom/index_group_usermanagement.html', # optional
#        'classes': ['collapse-open'], # optional
#        'show_apps': True, # optional
    },
    1: {
        'name': _('game'),
        'apps': ['messages', 'game'],
    },
    2: {
        'name': _('system'),
        'apps': ['system', 'notification'],
    },
    3: {
        'name': _('record'),
        'apps': ['record', ],
    },
    4: {
        'name': _('grappelli'),
        'apps': ['grappelli'],
    },
    5: {
        'name': _('site'),
        'apps': ['sites'],
    },
    6: {
        'name': _('casino'),
        'apps': ['casino'],
    },
}
#admin.site.collections = {
#    0: {
#        'title': 'User Admin',
#        'groups': [0, 1]
#    },
#}
urlpatterns = patterns('',
    url(r'^chat/', 'game.views.showchats', name = 'chat'),
    url(r'^captcha/', include('captcha.urls')),
    url(r'^messages/', include('messages.urls')),
    url(r'^registration/', include('registration.backends.default.urls')),
    url(r'^tinymce/', include('tinymce.urls')),
    url(r'^grappelli/', include('grappelli.urls')),
    url(r'^admin/filebrowser/', include('filebrowser.urls')),
    url(r'^assets/(?P<path>.*)$', 'django.views.static.serve',
        {'document_root': 'e:/AppServ/pys/crims/assets'}),
    url(r'^admin/', include(admin.site.urls)),
    url(r'^messages/', include('messages.urls')),
    url(r'^$', home(), name = 'index'),
)
