from django.db import models
from django.utils.translation import ugettext_lazy as _

from system.models import Weapon, Hooker

class Promotion(models.Model):
    title = models.CharField(_('title'), max_length = 100)
    description = models.CharField(_('description'), max_length = 255)
    cash = models.PositiveSmallIntegerField(_('cash'))
    credits = models.PositiveSmallIntegerField(_('credits'))
    weapon = models.ForeignKey(Weapon, verbose_name = _('weapon'), blank = True, null = True)
    hooker = models.ForeignKey(Hooker, verbose_name = _('hooker'), blank = True, null = True)
    created = models.DateTimeField(_('created'), editable = False, auto_now_add = True)

    def __unicode__(self):
        return self.title

    class Meta:
        app_label = 'game'
        verbose_name = _('promotion')
        verbose_name_plural = _('promotions')
