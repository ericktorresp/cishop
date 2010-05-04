from django.db import models
from django.utils.translation import ugettext_lazy as _

TYPES = (
   ('condom', _('condom')),
   ('hooker', _('hooker')),
   ('weapon', _('weapon')),
   ('building', _('building')),
)
class Benefit(models.Model):
    title = models.CharField(_('title'), max_length=100)
    description = models.TextField(_('description'))
    type = models.CharField(_('type'), max_length=100, choices=TYPES)
    credits = models.SmallIntegerField(_('credits'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('benefit')
        verbose_name_plural = _('benefits')
        app_label = 'system'
        ordering = ['id', ]
