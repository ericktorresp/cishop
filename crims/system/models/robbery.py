from django.db import models
from django.utils.translation import ugettext_lazy as _
from system.fields import SeparatedValuesField

ROBBERY_TYPE = (
    ('single', _('Single')),
    ('gang', _('Gang')),
)
class Robbery(models.Model):
    title = models.CharField(_('title'), max_length=100)
    stamina = models.SmallIntegerField(_('stamina'))
    difficulty = models.SmallIntegerField(_('difficulty'))
    type = models.CharField(_('type'), max_length=10, choices=ROBBERY_TYPE, default='single')
    intelligence_min = models.DecimalField(_('intelligence min'), decimal_places=4, max_digits=10)
    intelligence_max = models.DecimalField(_('intelligence max'), decimal_places=4, max_digits=10)
    strength_min = models.DecimalField(_('strength min'), decimal_places=4, max_digits=10)
    strength_max = models.DecimalField(_('strength max'), decimal_places=4, max_digits=10)
    charisma_min = models.DecimalField(_('charisma min'), decimal_places=4, max_digits=10)
    charisma_max = models.DecimalField(_('charisma max'), decimal_places=4, max_digits=10)
    tolerance_min = models.DecimalField(_('tolerance min'), decimal_places=4, max_digits=10)
    tolerance_max = models.DecimalField(_('tolerance max'), decimal_places=4, max_digits=10)
    cash_min = models.FloatField(_('cash min'))
    cash_max = models.FloatField(_('cash max'))
    members = models.SmallIntegerField(blank=True, null=True, default=0)
    created = models.DateTimeField(_('created'), editable=False, auto_now_add=True)
    attribute_range = SeparatedValuesField(_('attribute range'), token=',', max_length=50)
    cash_range = SeparatedValuesField(_('cash range'), token=',', max_length=50)
        
    def __unicode__(self):
        return self.title
        
    class Meta:
        app_label = 'system'
        ordering = ['id']
        verbose_name = _('Robbery')
        verbose_name_plural = _('Robberies')
