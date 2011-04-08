from django.conf.urls.defaults import *
from bank.forms import UserDepositForm1, UserDepositForm2, UserDepositFormWizard

urlpatterns = patterns('',
    (r'^deposit/$', UserDepositFormWizard([UserDepositForm1, UserDepositForm2])),
)