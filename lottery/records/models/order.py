from django.db import models
from django.contrib.auth.models import User

class Order(models.Model):
    user = models.ForeignKey(User, related_name='user')
#    supperior = models.ForeignKey(User, related_name='supperior', null=True, blank=True, editable=False)
    created = models.DateTimeField(editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.user.username + '\'s Order #' + self.id;

    class Meta:
        app_label = 'records'
