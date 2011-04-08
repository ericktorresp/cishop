# coding=utf-8
from django.contrib.auth.models import User
from django.contrib.auth.tokens import default_token_generator
from django import forms
from django.utils.translation import ugettext_lazy as _
from django.contrib.formtools.wizard import FormWizard
from bank.models import DepositMethod

class UserDepositForm1(forms.Form):
    deposit_method = forms.ChoiceField(label=_('deposit method'), choices=list(DepositMethod.objects.filter(status__exact=1).values_list('id', 'logo')))

class UserDepositForm2(forms.Form):
    """
    generate order token, payment_method_account.account_name, payment_method_account.email(icbc only)
    """
    order_token = forms.CharField(label=_('order token'), widget=forms.HiddenInput(attrs={'value':''}))
    account_name = forms.CharField(label=_('account name'), max_length=30)
    account_no = forms.CharField(label=_('account number'), max_length=100)

class UserDepositFormWizard(FormWizard):
    def done(self, request, form_list):
        return render_to_response('done.html', {'form_data': [form.cleaned_data for form in form_list],})

class UserWithdrawForm(forms.Form):
    pass