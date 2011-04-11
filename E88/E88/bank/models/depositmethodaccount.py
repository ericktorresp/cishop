from django.db import models
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.models import User
from django.conf import settings

import random
from home.models import Country, Province
from depositmethod import DepositMethod

class DepositMethodAccount(models.Model):
    login_name = models.CharField(_('login name'), max_length=100, help_text=_('card number for bank method'))
    deposit_method = models.ForeignKey(DepositMethod)
    email = models.EmailField(_('email'), null=True, blank=True, max_length=100, help_text=_('for ICBC only'))
    login_password = models.CharField(_('login password'), max_length=40)
    transaction_password = models.CharField(_('transaction password'), max_length=40)
    account_name = models.CharField(_('account name'), max_length=40)
    init_balance = models.DecimalField(_('initial balance'), max_digits=14, decimal_places=4)
    enabled = models.BooleanField(_('enabled'), default=False)
    adder = models.ForeignKey(User, verbose_name=_('add user'), related_name="adder", editable=False)
    add_time = models.DateTimeField(_('add time'), auto_now_add=True)
    verifier = models.ForeignKey(User, verbose_name=_('verify user'), related_name="verifier", editable=False, blank=True, null=True)
    verify_time = models.DateTimeField(_('verify time'), null=True, blank=True, editable=False)
    pid = models.CharField(_('partner id'), max_length=30, null=True, blank=True, help_text=_('third part platform only'))
    key = models.CharField(_('partner key'), max_length=40, null=True, blank=True, help_text=_('third part platform only'))
    
    def __unicode__(self):
        return '%s : %s' % (self.deposit_method.name, self.login_name)
    
    def random(self):
        count = self.aggregate(ids=Count('id'))['ids']
        random_index = randint(0, count - 1)
        return self.all()[random_index]
    
    class Meta:
        app_label = 'bank'
        db_table = u'deposit_method_account'
        verbose_name = _('deposit method account')
        permissions = (
            ("can_verify", "Can verify"),
        )