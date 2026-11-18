using OdontoCare.Application.Features.Estoque.DTOs;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Features.Estoque.Mappings;

public static class EstoqueMappings
{
    public static MaterialDto ToDto(this Material m) => new(m.IdMaterial, m.NomeMaterial, m.Descricao,
            m.Quantidade, m.EstoqueMinimo, m.Unidade, m.Quantidade <= m.EstoqueMinimo);
}
