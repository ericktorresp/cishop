from django.conf.urls.defaults import patterns, include
from django.contrib import admin
from django.views.generic.simple import direct_to_template
admin.autodiscover()

urlpatterns = patterns('',
    # Example:
    # (r'^mysite/', include('mysite.foo.urls')),

    # Uncomment the admin/doc line below and add 'django.contrib.admindocs' 
    # to INSTALLED_APPS to enable admin documentation:
    # (r'^admin/doc/', include('django.contrib.admindocs.urls')),
#    (r'^polls/$', 'index'),
#    (r'^polls/(?P<poll_id>\d+)/$', 'detail'),
#    (r'^polls/(?P<poll_id>\d+)/results/$', 'results'),
#    (r'^polls/(?P<poll_id>\d+)/vote/$', 'vote'),
    # Uncomment the next line to enable the admin:
    (r'^captcha/', include('captcha.urls')),
    (r'^assets/(?P<path>.*)$', 'django.views.static.serve',
        {'document_root': 'e:/AppServ/pys/mysite/assets'}),
    (r'^registration/', include('registration.backends.default.urls')),
    (r'^tinymce/', include('tinymce.urls')),
    (r'^grappelli/', include('grappelli.urls')),
    (r'^polls/', include('mysite.polls.urls')),
    (r'^channels/', include('mysite.channels.urls')),
    (r'^lotteries/', include('mysite.lotteries.urls')),
    (r'^admin/filebrowser/', include('filebrowser.urls')),
    (r'^admin/', include(admin.site.urls)),
    (r'^$', direct_to_template, { 'template': 'index.html' }, 'index'),
)
