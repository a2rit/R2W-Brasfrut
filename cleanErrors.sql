/****** Deleta todos os erros *******/
DELETE FROM [dbo].[erros]
      WHERE id > 0
GO

UPDATE [dbo].[nfc_itens]
   SET [erro_id] = null
 WHERE [erro_id] is not null
GO

UPDATE [dbo].[nfc]
   SET [erro_id] = null
 WHERE [erro_id] is not null
GO

UPDATE [dbo].[colibri_nfc_pagamentos]
   SET [erro_id] = null
 WHERE [erro_id] is not null
GO

UPDATE [dbo].[intern_consumption_items]
SET [erro_id] = null
WHERE [erro_id] is not null
GO

UPDATE [dbo].[intern_consumption]
SET [erro_id] = null
WHERE [erro_id] is not null
GO
/****** Deleta todos os erros *******/

/****** Fix Model erro ***********/
UPDATE [dbo].[erros]
SET [model] = 'App\Models\NFCe'
WHERE model = 'NFCe'
GO

UPDATE [dbo].[erros]
SET [model] = 'App\Models\NFCe\Item'
WHERE model = 'NFCe\Item'
GO
/****** Fix Model erro ***********/

SELECT erros.[id]
      ,[model]
      ,[model_id]
      ,[mensagem]
      ,[lido],
	  erro_id,
	  erros.created_at, erros.updated_at
  FROM [SBO_R2W_SAP_PRODUCAO].[dbo].[erros]
  left join [nfc_itens] on nfc_itens.id = erros.model_id
  where model = 'NFCe\Item' and erro_id is null

  SELECT erros.[id]
      ,[model]
      ,[model_id]
      ,[mensagem]
      ,[lido],
	  erro_id,	  erros.created_at, erros.updated_at
  FROM [SBO_R2W_SAP_PRODUCAO].[dbo].[erros]
  left join [nfc] on nfc.id = erros.model_id
  where model = 'NFCe' and erro_id is null

/************* Refazer OP **********************/
UPDATE [nfc_itens]
SET [status_op] = null, [codigo_op] = null
WHERE codigo_op = 1127615
