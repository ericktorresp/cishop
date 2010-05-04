from django.db import models
from django.utils.translation import ugettext_lazy as _

ATTRIBUTES = (
    ('intelligence', _('intelligence')),
    ('charisma', _('charisma')),
    ('tolerance', _('tolerance')),
    ('strength', _('strength')),
    ('addiction', _('addiction')),
)

class Hospital(models.Model):
    title = models.CharField(_('title'), max_length=100)
    type = models.CharField(_('type'), max_length=20, choices=ATTRIBUTES)
    price = models.SmallIntegerField(_('price'))
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        verbose_name = _('hospital')
        verbose_name_plural = _('hospitals')
        app_label = 'system'
        ordering = ['id', ]
