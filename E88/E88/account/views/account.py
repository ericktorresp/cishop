# coding=utf-8
from django.http import Http404, HttpResponse, HttpResponseRedirect
from django.contrib.auth.models import User
from django.utils.translation import ugettext as _
from django.contrib.sites.models import get_current_site
from django.contrib.auth.decorators import login_required
from django.utils.decorators import method_decorator
from home.views import BaseView
from e8.account.forms import UserUpdateEmailForm
from django.views.generic import FormView
from django.core.urlresolvers import reverse
from django.utils.functional import lazy 
from home.models import Country

from bank.models import PayMethod

class LoginNeededView(BaseView):
    template_name = 'index.html'
        
    @method_decorator(login_required)
    def dispatch(self, *args, **kwargs):
        return super(LoginNeededView, self).dispatch(*args, **kwargs)
    
class AccountIndexView(LoginNeededView):
    template_name = 'index.html'
    
    def get_context_data(self, **kwargs):
        context = super(AccountIndexView, self).get_context_data(**kwargs)
        context['country_codes'] = Country.objects.all()
        if self.request.method == 'GET':
            context['form'] = UserUpdateEmailForm(instance=self.request.user)
        else:
            context['form'] = UserUpdateEmailForm(data=self.request.POST)
        return context
        
    def post(self, request, *args, **kwargs):
        if request.POST['email']==request.user.email:
            return self.get(self, request, *args, **kwargs)
        form = UserUpdateEmailForm(self.request.POST)
        if form.is_valid():
            form.save(request=request)
            return HttpResponseRedirect(reverse('account_index'))
        return self.get(self, request, *args, **kwargs)
    
class AccountDepositView(LoginNeededView):
    template_name = 'deposit.html'
    
    def get_context_data(self, **kwargs):
        context = super(AccountDepositView, self).get_context_data(**kwargs)
        context['paymethods'] = PayMethod.objects.filter(status__exact=1)
        return context

class AccountDeposit2View(LoginNeededView):
    template_name = 'deposit_2.html'
    
class AccountWithdrawView(LoginNeededView):
    template_name = 'withdraw.html'
    
class AccountVerifyView(LoginNeededView):
    pass

class AccountReferralView(LoginNeededView):
    pass

class AccountPasswordView(LoginNeededView):
    template_name = 'password_change_form.html'
    
class AccountHistoryView(LoginNeededView):
    pass

class AccountMycardView(LoginNeededView):
    pass

class AccountSecurepasswordView(LoginNeededView):
    pass
