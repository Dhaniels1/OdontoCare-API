using OdontoCare.Application.Features.Financeiro.DTOs;

namespace OdontoCare.Application.Features.Financeiro.Interfaces;

public interface IPainelService
{
    Task<PainelDto> ObterAsync(int? usuarioId, DateTime hoje);
}
