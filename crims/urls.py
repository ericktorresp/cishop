from django.conf.urls.defaults import *
from django.views.generic.simple import direct_to_template
from django.contrib import admin
from grappelli.sites import GrappelliSite
from django.utils.translation import ugettext_lazy as _

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
        'name': _('grappelli'),
        'apps': ['grappelli'],
    },
    4: {
        'name': _('site'),
        'apps': ['sites'],
    },
}
#admin.site.collections = {
#    0: {
#        'title': 'User Admin',
#        'groups': [0, 1]
#    },
#}
urlpatterns = patterns('',
    (r'^captcha/', include('captcha.urls')),
    (r'^messages/', include('messages.urls')),
    (r'^registration/', include('registration.backends.default.urls')),
    (r'^tinymce/', include('tinymce.urls')),
    (r'^grappelli/', include('grappelli.urls')),
    (r'^admin/filebrowser/', include('filebrowser.urls')),
    (r'^assets/(?P<path>.*)$', 'django.views.static.serve',
        {'document_root': 'e:/AppServ/pys/crims/assets'}),
     (r'^admin/', include(admin.site.urls)),
    (r'^$', direct_to_template, { 'template': 'index.html' }, 'index'),
)
