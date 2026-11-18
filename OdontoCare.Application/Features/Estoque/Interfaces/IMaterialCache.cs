using OdontoCare.Application.Features.Estoque.DTOs;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Features.Estoque.Interfaces;

public interface IMaterialCache
{
    Task<List<MaterialDto>> ObterOuCarregarAsync(Func<Task<List<MaterialDto>>> carregar);
    void Invalidar();
}
