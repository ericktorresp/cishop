from django.db import models
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.models import User
from django.conf import settings

from home.models import Country, Province

class Bank(models.Model):
    code = models.CharField(_('code'), max_length=10)
    name = models.CharField(_('name'), max_length=30)
    logo = models.ImageField(upload_to='images/bank', verbose_name=_('logo'), max_length=100)
    
    def __unicode__(self):
        return self.name

    def img_logo(self):
        return '<img src="%s%s">' % (settings.MEDIA_URL, self.logo)
    
    img_logo.allow_tags=True
    img_logo.short_description = 'logo image'
    
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
    init_balance = models.DecimalField(_('Init Balance'), max_digits=14, decimal_places=4)
    login_pwd = models.CharField(_('Login Password'), max_length=30)
    transaction_pwd = models.CharField(_('Transaction Password'), max_length=30)
    country = models.ForeignKey(Country, verbose_name = _('Country'))
    province = models.ForeignKey(Province, verbose_name = _('Province'))
    discriminator = models.CharField(_('discriminator'), max_length=10, choices=(('withdraw',_('Withdraw')),('internal',_('Internal'))))
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

PAYMETHOD_STATUS = (
    (0, _('disabled')),
    (1, _('enabled')),
    (2, _('deleted')),
)
class PaymentMethod(models.Model):
    name = models.CharField(_('name'), max_length=20)
    alias = models.CharField(_('alias'), max_length=10)
    currency = models.CharField(_('currency'), max_length=3)
    discriminator = models.CharField(_('discriminator'), max_length=10, choices=settings.PAYMENTMETHOD_TYPE)
    note = models.CharField(_('note'), max_length=255)
    instruction = models.TextField(_('instruction'), max_length=1000)
    status = models.SmallIntegerField(_('status'), max_length=1, choices=PAYMETHOD_STATUS, default=0)
    url = models.URLField(_('url'), max_length=200, verify_exists=False)
    logo = models.ImageField(upload_to='images/payment', max_length=100)
    min_deposit = models.DecimalField(_('min deposit'), max_digits=14, decimal_places=4)
    max_deposit = models.DecimalField(_('max deposit'), max_digits=14, decimal_places=4)
    adder = models.ForeignKey(User, editable=False)
    add_time = models.DateTimeField(_('add time'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.name

    def img_logo(self):
        return '<img src="%s%s">' % (settings.MEDIA_URL, self.logo)
    img_logo.allow_tags=True
    img_logo.short_description = 'logo image'
            
    class Meta:
        db_table = u'payment_method'
        verbose_name = _('payment method')

class PaymentMethodAccount(models.Model):
    login_name = models.CharField(_('login name'), max_length=100, help_text=_('card number for bank method'))
    payment_method = models.ForeignKey(PaymentMethod)
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
        return '%s : %s' % (self.payment_method.name, self.login_name)
    
    class Meta:
        db_table = u'payment_method_account'
        verbose_name = _('payment method account')
        permissions = (
            ("can_verify", "Can verify"),
        )