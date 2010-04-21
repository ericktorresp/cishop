from django.db import models
from django.utils.translation import ugettext_lazy, ugettext as _

TYPES = (
   ('condom', _('Condom')),
   ('hooker', _('Hooker')),
   ('weapon', _('Weapon')),
   ('building', _('Building')),
)
class Benefit(models.Model):
    title = models.CharField(max_length=100)
    description = models.TextField()
    type = models.CharField(max_length=100, choices=TYPES)
    credits = models.SmallIntegerField()
    created = models.DateTimeField(editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'
        ordering = ['id', ]
