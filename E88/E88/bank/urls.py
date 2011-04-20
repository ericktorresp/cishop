from django.conf.urls.defaults import *
from bank.forms import UserDepositForm1, UserDepositForm2, UserDepositFormWizard
from bank.views import receive

urlpatterns = patterns('',
    url(r'^deposit/$', UserDepositFormWizard([UserDepositForm1, UserDepositForm2]), name='deposit'),
    url(r'^receive$', receive, name="sms_receive"),
)