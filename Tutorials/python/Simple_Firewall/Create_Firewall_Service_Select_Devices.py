from msa_sdk.variables import Variables
from msa_sdk.msa_api import MSA_API

dev_var = Variables()
dev_var.add('device', var_type='Device')

context = Variables.task_call(dev_var)

ret = MSA_API.process_content('ENDED', 'workflow initialised', context, True)
print(ret)
