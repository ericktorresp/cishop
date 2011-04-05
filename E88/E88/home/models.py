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
    users = models.ManyToManyField(User, db_table='user_domain', blank=True, null=True)
    
    def __unicode__(self):
        return self.domain
    
    class Meta:
        db_table = u'domain'
        verbose_name = _('Domain')
        verbose_name_plural = _('Domains')

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