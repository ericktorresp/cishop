from django.db import models
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.models import User
import datetime

# Country Model
class Country(models.Model):
    iso = models.CharField(unique=True, max_length=6, primary_key=True)
    name = models.CharField(max_length=240)
    printable_name = models.CharField(max_length=240)
    cn_name = models.CharField(max_length=240)
    iso3 = models.CharField(max_length=9, blank=True, null=True)
    numcode = models.IntegerField(null=True, blank=True)
    
    def __unicode__(self):
        return self.printable_name
    
    class Meta:
        db_table = u'country'
        verbose_name = _('Country')
        verbose_name_plural = _('Countries')
        ordering = ['name',]
        
    class Admin:
        pass

# Province Model    
class Province(models.Model):
    name = models.CharField(_('Title'), max_length=100)
    country = models.ForeignKey(Country, verbose_name=_('Country'), to_field='iso')

    def __unicode__(self):
        return self.name
    
    class Meta:
        db_table = u'province'
        ordering = ['id', ]
        verbose_name = _('Province')
        verbose_name_plural = _('Provinces')

# City Model
class City(models.Model):
    city = models.CharField(_('City'), max_length=100)
    province = models.ForeignKey(Province, verbose_name=_('Province'))
    
    def __unicode__(self):
        return self.city
    
    class Meta:
        db_table = u'city'
        verbose_name = _('City')
        verbose_name_plural = _('Cities')

# Domain model
class Domain(models.Model):
    enabled = models.BooleanField(_('Enabled'))
    domain = models.URLField(_('URL'), max_length=255, verify_exists=False)
    user_domain = models.ManyToManyField(User, db_table='user_domain', blank=True, null=True)
    
    def __unicode__(self):
        return self.domain
    
    class Meta:
        db_table = u'domain'
        verbose_name = _('Domain')
        verbose_name_plural = _('Domains')
        
# Bank model
class Bank(models.Model):
    code = models.CharField(_('code'), max_length=10)
    name = models.CharField(_('name'), max_length=30)
    logo = models.ImageField(upload_to='images/bank', verbose_name=_('logo'), max_length=100)
    
    def __unicode__(self):
        return self.name
    
    class Meta:
        db_table = u'bank'
        verbose_name = _('Bank')
        verbose_name_plural = _('Banks')
        
# Card model
class Card(models.Model):
    card_no = models.CharField(_('Card No.'), max_length=87)
    bank = models.ForeignKey(Bank, verbose_name=_('Bank'))
    alias = models.CharField(_('Alias'), max_length=30)
    currency = models.CharField(_('Currency'), max_length=5)
    account_name = models.CharField(_('Account Name'), max_length=20)
    add_time = models.DateTimeField(auto_now_add=True,verbose_name=_('Add Time'))
    enabled = models.BooleanField(_('Enabled'), default=False)
    email = models.EmailField(_('Email'), max_length=100)
    init_balance = models.DecimalField(_('Init Balance'), max_digits=14, decimal_places=4)
    login_pwd = models.CharField(_('Login Password'), max_length=30)
    transaction_pwd = models.CharField(_('Transaction Password'), max_length=30)
    verify_time = models.DateTimeField(_('Verify Time'),blank=True, null=True, editable=False)
    verifier = models.ForeignKey(User, verbose_name=_('Verifier'), null=True, blank=True, editable=False)
    country = models.ForeignKey(Country, verbose_name = _('Country'))
    province = models.ForeignKey(Province, verbose_name = _('Province'))
    discriminator = models.CharField(_('discriminator'), max_length=10, choices=((_('Withdraw'),'withdraw'),(_('Deposit'),'deposit'),))
    
    def __unicode__(self):
        return _("%s: %s") % (self.bank.name, self.card_no)
    
    class Meta:
        db_table = u'card'
        verbose_name = _('Card')
        verbose_name_plural = _('Cards')
        permissions = (
            ("can_verify", "Can verify"),
        )
        
# Channel Model
class Channel(models.Model):
    enabled = models.BooleanField(_('Enabled'), default = True)
    name = models.CharField(_('Name'), max_length = 30)
    path = models.CharField(_('Path'), max_length = 90)
    user_channel = models.ManyToManyField(User, db_table='user_channel', blank=True, null=True)
    
    def __unicode__(self):
        return self.name
    
    class Meta:
        db_table = u'channel'
        verbose_name = _('Channel')
        verbose_name_plural = _('Channels')
        
# Announcement Model
class Announcement(models.Model):
    subject = models.CharField(_('Subject'), max_length=255)
    content = models.TextField(_('Content'), max_length=3000)
    author = models.ForeignKey(User, verbose_name=_('Author'), related_name='author', editable=False)
    add_time = models.DateTimeField(_('Add Time'), auto_now_add=True)
    verifier = models.ForeignKey(User, verbose_name=_('Verifier'), null=True, blank=True, editable=False)
    verify_time = models.DateTimeField(_('Verify Time'), null=True, blank=True, editable=False)
    deleted = models.BooleanField(_('Delete'))
    sticked = models.BooleanField(_('Stick'))
    channel = models.ForeignKey(Channel, verbose_name=_('Channel'))
    
    def __unicode__(self):
        return self.subject
    
    def verify(self):
        if request.user.has_perm("can_verify"):
            self.verifier = request.user.pk
            self.verify_time = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            self.save()
    
    def stick(self):
        self.sticked = True
        self.save()
        
    def delete(self):
        self.deleted = True
        self.save()
    
    class Meta:
        db_table = u'announcement'
        verbose_name = _('Announcement')
        verbose_name_plural = _('Announcements')
        permissions = (
            ("can_verify", "Can verify"),
            ("can_stick", "Can stick"),
        )

PAYMETHOD_STATUS = (
    (0, _('disabled')),
    (1, _('enabled')),
    (2, _('deleted')),
)    
# Paymethod Model
class PayMethod(models.Model):
    name = models.CharField(_('Name'), max_length=10)
    alias = models.CharField(_('Alias'), max_length=10)
    currency = models.CharField(_('Currency'), max_length=3)
    deposit_note = models.CharField(_('Deposit Note'), max_length=20)
    withdraw_note = models.CharField(_('Withdraw Note'), max_length=20)
    min_deposit = models.DecimalField(_('Min Deposit each time'), max_digits=14, decimal_places=4)
    max_deposit = models.DecimalField(_('Max Deposit each time'), max_digits=14, decimal_places=4)
    under_deposit_fee_money_per_time = models.DecimalField(_('Deposit Fee(money) per time under the dividing line'), max_digits=14, decimal_places=4)
    under_deposit_fee_percent_per_time = models.DecimalField(_('Deposit Fee(percent) per time under the dividing line'), max_digits=5, decimal_places=2)
    deposit_fee_dividing = models.DecimalField(_('Deposit Fee dividing line'), max_digits=14, decimal_places=4)
    above_deposit_fee_money_per_time = models.DecimalField(_('Deposit Fee(money) per time above the dividing line'), max_digits=14, decimal_places=4)
    above_deposit_fee_percent_per_time = models.DecimalField(_('Deposit Fee(percent) per time above the dividing line'), max_digits=5, decimal_places=2)
    min_withdraw = models.DecimalField(_('Min Withdraw each time'), max_digits=14, decimal_places=4)
    max_withdraw = models.DecimalField(_('Max Withdraw each time'), max_digits=14, decimal_places=4)
    under_withdraw_fee_money_per_time = models.DecimalField(_('Withdraw Fee(money) per time under the dividing line'), max_digits=14, decimal_places=4)
    under_withdraw_fee_percent_per_time = models.DecimalField(_('Withdraw Fee(percent) per time under the dividing line'), max_digits=5, decimal_places=2)
    withdraw_fee_dividing = models.DecimalField(_('Withdraw Fee dividing line'), max_digits=14, decimal_places=4)
    above_withdraw_fee_money_per_time = models.DecimalField(_('Withdraw Fee(money) per time above the dividing line'), max_digits=14, decimal_places=4)
    above_withdraw_fee_percent_per_time = models.DecimalField(_('Withdraw Fee(percent) per time above the dividing line'), max_digits=5, decimal_places=2)
    pay_for_platform_deposit_percent = models.DecimalField(_('Pay to platform for each deposit(percent)'), max_digits=5, decimal_places=2)
    min_pay_for_platform_deposit = models.DecimalField(_('Min pay to platform for deposit'), max_digits=14, decimal_places=4)
    max_pay_for_platform_deposit = models.DecimalField(_('Max pay to platform for deposit'), max_digits=14, decimal_places=4)
    pay_for_platform_withdraw_percent = models.DecimalField(_('Pay to platform for each withdraw(percent)'), max_digits=5, decimal_places=2)
    min_pay_for_platform_withdraw = models.DecimalField(_('Min pay to platform for withdraw'), max_digits=14, decimal_places=4)
    max_pay_for_platform_withdraw = models.DecimalField(_('Max pay to platform for withdraw'), max_digits=14, decimal_places=4)
    balance = models.DecimalField(_('Current balance'), max_digits=14, decimal_places=4)
    times_limit = models.SmallIntegerField(_('Opration times limit'), max_length=2)
    platform_host = models.CharField(_('Platform Host'), max_length=100, null=True, blank=True)
    platform_deposit_url = models.CharField(_('Platform deposit url'), max_length=100, null=True, blank=True)
    platform_withdraw_url = models.CharField(_('Platform withdraw url'), max_length=100, null=True, blank=True)
    platform_query_url = models.CharField(_('Platform query url'), max_length=100, null=True, blank=True)
    receive_host = models.CharField(_('Receive host'), max_length=100, null=True, blank=True)
    receive_url = models.CharField(_('Receive url'), max_length=100, null=True, blank=True)
    receive_url_continued = models.CharField(_('continued receive url'), max_length=100, null=True, blank=True)
    status = models.SmallIntegerField(_('Status'), max_length=1, choices=PAYMETHOD_STATUS, default=0)
    introdution = models.TextField(_('introdution'), max_length=3000)
    platform_require_encoding = models.CharField(_('Require encoding'), max_length=10, null=True, blank=True)
    platform_attr = models.SmallIntegerField(_('platform attribute'), max_length=1)
    add_time = models.DateTimeField(_('add time'), editable=False, auto_now_add=True)
    cards = models.ManyToManyField(Card, blank=True, null=True)
    
    def __unicode__(self):
        return self.name
    
    class Meta:
        db_table = u'pay_method'
        verbose_name = _('Payment Method')
        verbose_name_plural = _('Payment Methods')
    