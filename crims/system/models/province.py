from django.db import models
from django.utils.translation import ugettext_lazy as _

class Province(models.Model):
    title = models.CharField(_('title'),max_length=100)
    icon = models.CharField(_('icon'),max_length=10)

    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'
        ordering = ['id', ]
        verbose_name = _('Province')
        verbose_name_plural = _('Provinces')