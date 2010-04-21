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
    intelligence_min = models.DecimalField(_('Min intelligence'), decimal_places=4, max_digits=4)
    intelligence_max = models.DecimalField(_('Max intelligence'), decimal_places=4, max_digits=4)
    
