from django.db import models
from django.utils.translation import ugettext_lazy as _

ROBBERY_TYPE = (
    ('single', _('Single')),
    ('gang', _('Gang')),
)
class Robbery(models.Model):
    title = models.CharField(_('title'), max_length=100)
    stamina = models.SmallIntegerField(_('stamina'))
    difficulty = models.SmallIntegerField(_('difficulty'))
    type = models.CharField(_('type'), max_length=10, choices=ROBBERY_TYPE, default='single')
    intelligence_min = models.DecimalField(_('intelligence min'), decimal_places=4, max_digits=4)
    intelligence_max = models.DecimalField(_('intelligence max'), decimal_places=4, max_digits=4)
    strength_min = models.DecimalField(_('strength min'), decimal_places=4, max_digits=4)
    strength_max = models.DecimalField(_('strength max'), decimal_places=4, max_digits=4)
    charisma_min = models.DecimalField(_('charisma min'), decimal_places=4, max_digits=4)
    charisma_max = models.DecimalField(_('charisma max'), decimal_places=4, max_digits=4)
    tolerance_min = models.DecimalField(_('tolerance min'), decimal_places=4, max_digits=4)
    tolerance_max = models.DecimalField(_('tolerance max'), decimal_places=4, max_digits=4)
    cash_min = models.DecimalField(_('cash min'), decimal_places=4, max_digits=4)
    cash_max = models.DecimalField(_('cash max'), decimal_places=4, max_digits=4)
    members = models.SmallIntegerField(blank=True, null=True, default=0)
    created = models.DateTimeField(editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.title
    def range(self, field):
        min_field = field + '_min'
        max_field = field + '_max'
        return "%s - %s" % (self.min_field, self.max_field)
    #range.short_description = _(field)
        
    class Meta:
        app_label = 'system'
        ordering = ['id']
        verbose_name = _('Robbery')
        verbose_name_plural = _('Robberies')
