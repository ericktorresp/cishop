from django.db import models

# =====================
# Account Type model
# =====================
class AccountType(models.Model):
    title = models.CharField(max_length=6)
    description = models.CharField(max_length=255)
    parent = models.ForeignKey('self', blank=True, null=True)
    is_foruser = models.BooleanField(default=True)
    is_plus = models.BooleanField(default=False)
    created = models.DateTimeField(auto_now_add=True, editable=False)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'accounts'
