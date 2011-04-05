from django.conf.urls.defaults import *

from account.views.account import *

urlpatterns = patterns('',
    url(r'^$', AccountIndexView.as_view(), name="account_index"),
#    url(r'^profile$', profile, name="account_profile"),
    url(r'^verify$', AccountVerifyView.as_view(), name="account_verify"),
    url(r'^deposit$', AccountDepositView.as_view(), name="account_deposit"),
    url(r'^deposit/(?P<pay_id>\d)$', AccountDeposit2View.as_view(), name="account_deposit2"),
    url(r'^withdraw$', AccountWithdrawView.as_view(), name="account_withdraw"),
    url(r'^referral$', AccountReferralView.as_view(), name="referral"),
    url(r'^password$', AccountPasswordView.as_view(), name="set_password"),
    url(r'^history$', AccountHistoryView.as_view(), name="account_history"),
    url(r'^mycard$', AccountMycardView.as_view(), name="mycard"),
    url(r'^securepwd$', AccountSecurepasswordView.as_view(), name="securepwd"),
)