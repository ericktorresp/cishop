from django.db import models
from django.contrib.auth.models import User
from django.utils.translation import ugettext_lazy as _

from game.models import UserBusiness

class UserBusinessDailyCount(models.Model):
    userbusiness = models.ForeignKey(UserBusiness, verbose_name=_('user business'))
    visitors = models.SmallIntegerField(_('visitors'), default=0)
    day = models.PositiveSmallIntegerField(_('day'))
    
    def __unicode__(self):
        return _('daily visitors for %s') % (self.userbusiness.title)

    class Meta:
        app_label = 'record'
        verbose_name = _('daily visitors')
        verbose_name_plural = _('daily visitors')
        unique_together = ('userbusiness', 'day')

class UserBusinessLog(models.Model):
    user = models.ForeignKey(User, verbose_name=_('vistor'))
    userbusiness = models.ForeignKey(UserBusiness, verbose_name=_('user business'))
    income = models.PositiveSmallIntegerField(_('income'))
    exited = models.BooleanField(_('exited'), default=False)
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return _('log for %s') % (self.userbusiness.title)
    
    class Meta:
        app_label = 'record'
        verbose_name = _('user business log')
        verbose_name_plural = _('user business logs')
        
    class Admin:
        ordering = ('-id',)

