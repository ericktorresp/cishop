# coding=utf-8
from django.contrib.auth.models import User
#from django.contrib.auth.tokens import default_token_generator
from django import forms
from django.utils.safestring import mark_safe
from django.utils.translation import ugettext_lazy as _
from django.contrib.formtools.wizard import FormWizard
from django.shortcuts import render_to_response
from django.template.context import RequestContext
from django.http import Http404, HttpResponse, HttpResponseRedirect

import datetime, random
from bank.models import DepositMethod, DepositMethodAccount, DepositLog, Cellphone

class UserDepositForm1(forms.Form):
    deposit_method = forms.ChoiceField(label=_('deposit method'), choices=DepositMethod.objects.filter(status__exact=1).values_list('id', 'logo'))

class UserDepositForm2(forms.Form):
    """
    generate order number, payment_method_account.account_name, payment_method_account.email(icbc only)
    """
    order_number = forms.CharField(label=_('order number'), max_length=15, widget=forms.TextInput(
        attrs={'readonly':'readonly'})
    )
    account_name = forms.CharField(label=_('account name'), max_length=30, widget=forms.TextInput(
        attrs={'readonly': 'readonly'})
    )
    account_no = forms.CharField(label=_('account number'), max_length=100, widget=forms.TextInput(
        attrs={'readonly': 'readonly'})
    )
    cellphone = forms.CharField(label=_('notice cellphone'), max_length=11, widget=forms.TextInput(
        attrs={'readonly': 'readonly'})
    )
    deposit_method_account = forms.IntegerField(widget=forms.HiddenInput)

class UserDepositFormWizard(FormWizard):
    step_field_name = 'step'
    def get_template(self, step):
        return 'deposit_%s.html' % step

    def render_template(self, request, form, previous_fields, step, context=None):
        context = context or {}
        if step == 0:
            context.update({'methods':DepositMethod.objects.filter(status__exact=1)})
        elif step == 1:
            account = DepositMethodAccount.objects.filter(deposit_method__exact=request.POST['0-deposit_method']).order_by('?')[0]
#            cellphone = Cellphone.objects.filter(enabled=True).order_by('?')[0]
            form.initial = {
                'order_number': datetime.datetime.now().strftime("%y%m%d%H%M%S")+str(random.randint(100,999)),
                'account_name': account.account_name,
                'account_no': account.email and account.email or account.login_name,
                'cellphone': account.cellphone,
                'deposit_method_account': account.id
            }
            context.update({
                'method': DepositMethod.objects.get(id__exact=request.POST['0-deposit_method'])
            })
        return render_to_response(self.get_template(step), dict(context,
            step_field=self.step_field_name,
            step0=step,
            step=step + 1,
            step_count=self.num_steps(),
            form=form,
            previous_fields=previous_fields
        ), context_instance=RequestContext(request))
    
    def process_step(self, request, form, step):
        pass
    
    def done(self, request, form_list):
        data = {}
        for form in form_list:
            data.update(form.cleaned_data)
        deposit_log = DepositLog.objects.create(
            order_number = data['order_number'],
            user = request.user,
            deposit_method = DepositMethod.objects.get(id__exact=data['deposit_method']),
            deposit_method_account = DepositMethodAccount.objects.get(id__exact=data['deposit_method_account']),
#            deposit_method_account_login_name = data['account_no'],
#            deposit_method_account_account_name = data['account_name'],
#            email = data['account_no'],
            status = 0,
#            cellphone = Cellphone.objects.get(number__exact=data['cellphone']),
        )

        return HttpResponse('ok, your deposit will done in minute.')

class UserWithdrawForm(forms.Form):
    pass