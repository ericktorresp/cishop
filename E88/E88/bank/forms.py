# coding=utf-8
from django.contrib.auth.models import User
from django.contrib.auth.tokens import default_token_generator
from django import forms
from django.utils.safestring import mark_safe
from django.utils.translation import ugettext_lazy as _
from django.contrib.formtools.wizard import FormWizard
from django.shortcuts import render_to_response
from django.template.context import RequestContext

from bank.models import DepositMethod

class HorizRadioRenderer(forms.RadioSelect.renderer):
    """ this overrides widget method to put radio buttons horizontally
        instead of vertically.
    """
    def render(self):
        """Outputs radios"""
        return mark_safe(u'\n'.join([u'%s\n' % w for w in self]))

class UserDepositForm1(forms.Form):
    deposit_method = forms.ChoiceField(label=_('deposit method'), choices=DepositMethod.objects.filter(status__exact=1).values_list('id', 'logo'), widget=forms.RadioSelect)

class UserDepositForm2(forms.Form):
    """
    generate order token, payment_method_account.account_name, payment_method_account.email(icbc only)
    """
    order_token = forms.CharField(label=_('order token'), widget=forms.HiddenInput(attrs={'value':''}))
    account_name = forms.CharField(label=_('account name'), max_length=30)
    account_no = forms.CharField(label=_('account number'), max_length=100)

class UserDepositFormWizard(FormWizard):
    extra_context = {'methods':DepositMethod.objects.filter(status__exact=1).values('id', 'logo', 'discriminator')}
    
    def get_template(self, step):
        return 'deposit_%s.html' % step

    def render_template(self, request, form, previous_fields, step, context=None):
        context = context or {}
        if step == 0:
            context.update({'methods':DepositMethod.objects.filter(status__exact=1)})
        return render_to_response(self.get_template(step), dict(context,
            step_field=self.step_field_name,
            step0=step,
            step=step + 1,
            step_count=self.num_steps(),
            form=form,
            previous_fields=previous_fields
        ), context_instance=RequestContext(request))
    
    def done(self, request, form_list):
        return render_to_response('done.html', {'form_data': [form.cleaned_data for form in form_list],})

class UserWithdrawForm(forms.Form):
    pass