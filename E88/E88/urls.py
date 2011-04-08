from django.conf.urls.defaults import *

# Uncomment the next two lines to enable the admin:
from django.contrib import admin
from django.conf import settings
from account.views import RegisterView, register_step2, register_confirm, register_done
#from account.views import register, register_step2, active, register_confirm, register_done
from dajaxice.core import dajaxice_autodiscover
from games.views import index

dajaxice_autodiscover()

admin.autodiscover()

urlpatterns = patterns('',
    # Example:
    # (r'^e8/', include('e8.foo.urls')),

    # Uncomment the admin/doc line below to enable admin documentation:
    # (r'^admin/doc/', include('django.contrib.admindocs.urls')),
    # url(r'^static/(?P<path>.*)$', 'django.views.static.serve',{'document_root': settings.MEDIA_ROOT}),

    # Uncomment the next line to enable the admin:
    url(r'^admin/', include(admin.site.urls)),
    url(r'^%s/' % settings.DAJAXICE_MEDIA_PREFIX, include('dajaxice.urls')),
    url(r'^$', include('home.urls')),
    url(r'^account/login$', 'django.contrib.auth.views.login', {'template_name':'login.html'}, name="login"),
    url(r'^account/logout$', 'django.contrib.auth.views.logout', {'template_name':'logged_out.html'}, name="logout"),
    url(r'^account/register$', RegisterView.as_view(), name='register'),
    url(r'^account/register/step2$', register_step2, name='register2'),
    url(r'^account/register/confirm$', register_confirm, name='register_confirm'),
    url(r'^account/register/done$', register_done, name='register_done'),
    url(r'^account/reset$', 'django.contrib.auth.views.password_reset', {'template_name':'password_reset_form.html'}, name='resetpwd'),
    url(r'^account/reset/(?P<uidb36>[0-9A-Za-z]+)-(?P<token>.+)$', 'django.contrib.auth.views.password_reset_confirm', {'template_name' : 'password_reset_confirm.html',  'post_reset_redirect': '/' }),
#    url(r'^account/active/(?P<uidb36>[0-9A-Za-z]+)-(?P<token>.+)$', active, {'post_reset_redirect': '/' }, name='activation'),
    url(r'^account/reset/done$', 'django.contrib.auth.views.password_reset_done', {'template_name':'password_reset_done.html'}, name='password_reset_done'),
    url(r'^account/', include('account.urls')),
    url(r'^games$', index(), name="games_index"),
    url(r'^bank/', include('bank.urls')),
)
