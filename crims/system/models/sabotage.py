from django.db import models
from django.utils.translation import ugettext_lazy as _

class Sabotage(models.Model):
    title = models.CharField(_('title'), max_length=100)
    stamina = models.SmallIntegerField(_('stamina'))
    difficulty = models.SmallIntegerField(_('difficulty'))
    expend = models.SmallIntegerField(_('expend'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'
        verbose_name = _('sabotage')
        verbose_name_plural = _('sabotages')
