from django.conf.urls.defaults import *

# Uncomment the next two lines to enable the admin:
from django.contrib import admin
admin.autodiscover()

urlpatterns = patterns('',
    (r'^captcha/', include('captcha.urls')),
    (r'^registration/', include('registration.backends.default.urls')),
    (r'^tinymce/', include('tinymce.urls')),
    (r'^grappelli/', include('grappelli.urls')),
    (r'^admin/filebrowser/', include('filebrowser.urls')),
    (r'^assets/(?P<path>.*)$', 'django.views.static.serve',
        {'document_root': '/Users/darkmoon/pys/crims/assets'}),
     (r'^admin/', include(admin.site.urls)),
)
