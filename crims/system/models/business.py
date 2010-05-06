from django.db import models
from filebrowser.fields import FileBrowseField
from system.models.drug import Drug, UserDrug
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

BUSINESS_TYPE = (('bar', _('bar')), ('club', _('club')))

class Business(models.Model):
    title = models.CharField(_('title'), max_length=100)
    photo = FileBrowseField(_('photo'), max_length=200, directory="business/", format="Image", extensions=['.jpg', '.gif', '.png'])
    max_vistors = models.SmallIntegerField(_('max vistors'))
    price = models.IntegerField(_('price'))
    expend = models.IntegerField(_('expend'))
    type = models.CharField(_('type'), max_length=20, choices=BUSINESS_TYPE)
    limit = models.SmallIntegerField(_('limit'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    user_business = models.ManyToManyField(User, through='UserBusiness')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('business')
        verbose_name_plural = _('business')
        app_label = 'system'
        ordering = ['id']

class UserBusiness(models.Model):
    user = models.ForeignKey(User)
    business = models.ForeignKey(Business, verbose_name=_('business'))
    title = models.CharField(_('title'), max_length=200, blank=True, null=True)
    description = models.CharField(_('description'), max_length=255, blank=True, null=True)
    max_respect = models.SmallIntegerField(_('max_respect'))
    entrance_fee = models.SmallIntegerField(_('entrance_fee'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    modified = models.DateTimeField(_('modified'), editable=False, auto_now=True)
    income = models.IntegerField(_('income'))
    closed = models.BooleanField(_('closed'), default=False)
    user_drug = models.ManyToManyField(UserDrug, verbose_name=_('drug'), through='UserBusinessDrug')
    
    def __unicode__(self):
        if self.title:
            return self.title
        else:
            return self.business.title
    
    def save(self):
        if not self.title:
            self.title = _("%s's %s") % (self.user.username, self.business.title)
        super(UserBusiness, self).save()
    
    class Meta:
        verbose_name = _('user\'s business')
        verbose_name_plural = _('user\'s businesses')
        db_table = 'user_business'
        app_label = 'system'

class UserBusinessDrug(models.Model):
    userbusiness = models.ForeignKey(UserBusiness, verbose_name=_('user\'s business'))
    userdrug = models.ForeignKey(UserDrug, verbose_name=_("user's drug"))
    price = models.SmallIntegerField(_('price'))
    sold = models.SmallIntegerField(_('sold'))
    removed = models.BooleanField(_('removed'), default=False)
    
    def __unicode__(self):
        return self.userdrug.title
    
    class Meta:
        app_label = 'system'
        verbose_name = _('user business drug')
        verbose_name = _('user business drugs')
        db_table = 'user_business_user_drug'
