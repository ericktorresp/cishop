from django.db import models
from filebrowser.fields import FileBrowseField
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _
import datetime

class Hooker(models.Model):
    title = models.CharField(_('title'), max_length=100)
    photo = FileBrowseField(_('photo'), max_length=200, directory="hooker/", format="Image", extensions=['.jpg', '.gif', '.png'])
    price = models.IntegerField(_('price'))
    expend = models.IntegerField(_('expend'))
    visitprice = models.IntegerField(_('visit price'))
    sickprobability = models.DecimalField(_('sick probability'), decimal_places=4, max_digits=4)
    is_random = models.BooleanField(_('system'), default=False)
    stamina = models.IntegerField(_('stamina'))
    spirit = models.SmallIntegerField(_('spirit'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    updated = models.DateTimeField(_('updated'), editable=False, auto_now=True)
    user_hooker = models.ManyToManyField(User, through='UserHooker')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('hooker')
        verbose_name_plural = _('hookers')
        app_label = 'system'
        ordering = ['id', ]

class UserHooker(models.Model):
    user = models.ForeignKey(User)
    hooker = models.ForeignKey(Hooker, verbose_name=_('hooker'))
    visitprice = models.SmallIntegerField(_('visit price'))
    expend = models.SmallIntegerField(_('expend'))
    income = models.IntegerField(_('income'), default=0)
    freetime = models.DateTimeField(_('freetime'), blank=True, null=True, default=datetime.datetime.now() + datetime.timedelta(minutes=2))
    
    def __unicode__(self):
        return self.hooker.title
    
    class Meta:
        verbose_name = _('user\'s hooker')
        verbose_name_plural = _('user\'s hookers')
        db_table = 'user_hooker'
        app_label = 'system'
