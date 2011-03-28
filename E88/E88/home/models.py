from django.db import models
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.models import User

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
    domain = models.CharField(max_length=255)
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
    logo = models.CharField(_('logo'), max_length=99)
    
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
    add_time = models.DateTimeField(_('Add Time'))
    enabled = models.BooleanField(_('Enabled'), default=True)
    email = models.EmailField(_('Email'), max_length=100)
    init_balance = models.DecimalField(_('Init Balance'), max_digits=14, decimal_places=4)
    login_pwd = models.CharField(_('Login Password'), max_length=30)
    transaction_pwd = models.CharField(_('Transaction Password'), max_length=30)
    verify_time = models.DateTimeField(_('Verify Time'))
    country = models.ForeignKey(Country, verbose_name = _('Country'))
    province = models.ForeignKey(Province, verbose_name = _('Province'))
    discriminator = models.CharField(_('discriminator'), max_length=10, choices=((_('Withdraw'),'withdraw'),(_('Deposit'),'deposit'),))
    
    def __unicode__(self):
        return _("%s card %s") % (self.bank.name, self.card_no)
    
    class Meta:
        db_table = u'card'
        verbose_name = _('Card')
        verbose_name_plural = _('Cards')
        
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
    author = models.ForeignKey(User, verbose_name=_('Author'), related_name='author')
    add_time = models.DateTimeField(_('Add Time'))
    verifier = models.ForeignKey(User, verbose_name=_('Verifier'), related_name='verifier')
    verify_time = models.DateTimeField(_('Verify Time'))
    deleted = models.BooleanField(_('Delete'))
    sticked = models.BooleanField(_('Stick'))
    channel = models.ForeignKey(Channel, verbose_name=_('Channel'))
    
    def __unicode__(self):
        return self.subject
    
    class Meta:
        db_table = u'announcement'
        verbose_name = _('Announcement')
        verbose_name_plural = _('Announcements')
    
    
    