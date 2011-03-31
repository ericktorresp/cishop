from django.conf.urls.defaults import *

from account.views import *

urlpatterns = patterns('',
    url(r'^$', account, name="account_index"),
    url(r'^profile$', profile, name="account_profile"),
    url(r'^verify$', verify, name="account_verify"),
    url(r'^deposit$', deposit, name="account_deposit"),
    url(r'^withdraw$', withdraw, name="account_withdraw"),
    url(r'^referral$', referral, name="referral"),
    url(r'^password$', password, name="set_password"),
    url(r'^history$', password, name="account_history"),
    url(r'^mycard$', mycard, name="mycard"),
    url(r'^securepwd$', securepwd, name="securepwd"),
)