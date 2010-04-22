from django.db import models
from filebrowser.fields import FileBrowseField
from system.models.drug import Drug
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

class Business(models.Model):
    title = models.CharField(_('title'), max_length=100)
    photo = FileBrowseField(_('photo'), max_length=200, directory="business/", format="Image", extensions=['.jpg', '.gif', '.png'])
    max_vistors = models.SmallIntegerField(_('max_vistors'))
    price = models.IntegerField(_('price'))
    expend = models.IntegerField(_('expend'))
    type = models.CharField(_('type'), max_length=20, choices=(('bar', _('Bar')), ('club', _('Club'))))
    limit = models.SmallIntegerField(_('limit'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    user_business = models.ManyToManyField(User, through='UserBusiness')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('Business')
        verbose_name_plural = _('Business')
        app_label = 'system'
        ordering = ['id']

class UserBusiness(models.Model):
    user = models.ForeignKey(User)
    business = models.ForeignKey(Business)
    title = models.CharField(_('title'), max_length=200, blank=True, null=True)
    description = models.CharField(_('description'), max_length=255, blank=True, null=True)
    max_respect = models.SmallIntegerField(_('max_respect'))
    entrance_fee = models.SmallIntegerField(_('entrance_fee'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    modified = models.DateTimeField(_('modified'), editable=False, auto_now=True)
    income = models.IntegerField(_('income'))
    closed = models.BooleanField(_('closed'), default=False)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('User\'s Business')
        db_table = 'user_business'
        app_label = 'system'
