from django.conf.urls.defaults import patterns, include

# Uncomment the next two lines to enable the admin:
from django.contrib import admin
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
    (r'^polls/', include('mysite.polls.urls')),
    (r'^channels/', include('mysite.channels.urls')),
    (r'^lotteries/', include('mysite.lotteries.urls')),
    (r'^admin/', include(admin.site.urls)),
)
