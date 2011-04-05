from django.db import models
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.models import User

from home.models import Country, Province

class Bank(models.Model):
    code = models.CharField(_('code'), max_length=10)
    name = models.CharField(_('name'), max_length=30)
    logo = models.ImageField(upload_to='images/bank', verbose_name=_('logo'), max_length=100)
    min_deposit = models.DecimalField(_('Minimal deposit amount'), max_digits=14, decimal_places=4)
    max_deposit = models.DecimalField(_('Maximal deposit amount'), max_digits=14, decimal_places=4)
    min_withdraw = models.DecimalField(_('Minimal withdraw amount'), max_digits=14, decimal_places=4)
    max_withdraw = models.DecimalField(_('Maximal withdraw amount'), max_digits=14, decimal_places=4)
    deposit_fee_dividing = models.DecimalField(_('Deposit Fee dividing line'), max_digits=14, decimal_places=4)
    under_dividing_deposit_fee = models.DecimalField(_('Under deposit dividing fee (money)'), max_digits=14, decimal_places=4)
    under_dividing_deposit_percent = models.DecimalField(_('Under deposit dividing fee (percent)'), max_digits=4, decimal_places=2)
    above_dividing_deposit_fee = models.DecimalField(_('Above deposit dividing fee (money)'), max_digits=14, decimal_places=4)
    above_dividing_deposit_percent = models.DecimalField(_('Above deposit dividing fee (percent)'), max_digits=4, decimal_places=2)
    withdraw_fee_dividing = models.DecimalField(_('Withdraw fee dividing line'), max_digits=14, decimal_places=4)
    under_dividing_withdraw_fee = models.DecimalField(_('Under withdraw dividing fee (money)'), max_digits=14, decimal_places=4)
    under_dividing_withdraw_percent = models.DecimalField(_('Under withdraw dividing fee (percent)'), max_digits=4, decimal_places=2)
    url = models.URLField(_('URL'), max_length=100)
    
    def __unicode__(self):
        return self.name
    
    class Meta:
        db_table = u'bank'
        verbose_name = _('Bank')
        verbose_name_plural = _('Banks')

class Card(models.Model):
    card_no = models.CharField(_('Card No.'), max_length=87)
    bank = models.ForeignKey(Bank, verbose_name=_('Bank'))
    alias = models.CharField(_('Alias'), max_length=30)
    currency = models.CharField(_('Currency'), max_length=5)
    account_name = models.CharField(_('Account Name'), max_length=20)
    email = models.EmailField(_('Email'), max_length=100)
    init_balance = models.DecimalField(_('Init Balance'), max_digits=14, decimal_places=4)
    login_pwd = models.CharField(_('Login Password'), max_length=30)
    transaction_pwd = models.CharField(_('Transaction Password'), max_length=30)
    country = models.ForeignKey(Country, verbose_name = _('Country'))
    province = models.ForeignKey(Province, verbose_name = _('Province'))
    discriminator = models.CharField(_('discriminator'), max_length=10, choices=((_('Withdraw'),'withdraw'),(_('Deposit'),'deposit'),(_('Internal'), 'internal'),))
    enabled = models.BooleanField(_('Enabled'), default=False)
    adder = models.ForeignKey(User, verbose_name=_('Adder'), editable=False, related_name='Adder')
    add_time = models.DateTimeField(auto_now_add=True,verbose_name=_('Add Time'))
    verify_time = models.DateTimeField(_('Verify Time'),blank=True, null=True, editable=False)
    verifier = models.ForeignKey(User, verbose_name=_('Verifier'), null=True, blank=True, editable=False, related_name='Verifier')
        
    def __unicode__(self):
        return _("%s: %s") % (self.bank.name, self.card_no)
    
    class Meta:
        db_table = u'card'
        verbose_name = _('Card')
        verbose_name_plural = _('Cards')
        permissions = (
            ("can_verify", "Can verify"),
        )
class Thirdpart(models.Model):
    name = models.CharField(_('Name'), max_length=20)
    pid = models.CharField(_('Partner ID'), max_length=20)
    key = models.CharField(_('Key'), max_length=100)
    url = models.URLField(_('Request Gateway'), max_length=255)
    logo = models.ImageField(upload_to='images/thirdpart', verbose_name=_('logo'), max_length=100)
    min_deposit = models.DecimalField(_('Minimal deposit amount'), max_digits=14, decimal_places=4)
    max_deposit = models.DecimalField(_('Maximal deposit amount'), max_digits=14, decimal_places=4)
    
    def __unicode__(self):
        return self.name
    
    class Meta:
        db_table = u'thirdpart'
        verbose_name = _('Third part')
        verbose_name_plural = _('Third parts')

class ThirdpartAccount(models.Model):
    thirdpart = models.ForeignKey(Thirdpart)
    account_name = models.CharField(_('Account name'), max_length=100)
    account_password = models.CharField(_('login name'), max_length=100)
    tranaction_password = models.CharField(_('Tranaction password'), max_length=100)
    init_balance = models.DecimalField(_('Initial balance'), max_digits=7, decimal_places=4)
    adder = models.ForeignKey(User, verbose_name=_('Adder'), editable=False, related_name='adder')
    add_time = models.DateTimeField(auto_now_add=True, verbose_name=_('Add time'))
    verifier = models.ForeignKey(User, verbose_name=_('Verifier'), related_name='verifier')
    verify_time = models.DateTimeField(_('Verify time'), blank=True, null=True, editable=False)
    
    def __unicode__(self):
        return '%s: %s' % (self.thirdpart.name, self.account_name)
    
    class Meta:
        db_table = u'thirdpart_account'
        verbose_name = _('Thirdpart account')
        verbose_name_plural = _('Thirdpart accounts')
        permissions = (
            ('can_verify', 'Can verify'),
        )

PAYMETHOD_STATUS = (
    (0, _('disabled')),
    (1, _('enabled')),
    (2, _('deleted')),
)    

class PayMethod(models.Model):
    name = models.CharField(_('Name'), max_length=10)
    alias = models.CharField(_('Alias'), max_length=10)
    currency = models.CharField(_('Currency'), max_length=3)
    note = models.CharField(_('Note'), max_length=255)
    introdution = models.TextField(_('introduction'), max_length=3000)
    status = models.SmallIntegerField(_('Status'), max_length=1, choices=PAYMETHOD_STATUS, default=0)
    add_time = models.DateTimeField(_('add time'), editable=False, auto_now_add=True)
    banks = models.ManyToManyField(Bank, null=True, blank=True)
    thirdparts = models.ManyToManyField(Thirdpart, null=True, blank=True)
        
    def __unicode__(self):
        return self.name
    
    class Meta:
        db_table = u'pay_method'
        verbose_name = _('Payment Method')
        verbose_name_plural = _('Payment Methods')   